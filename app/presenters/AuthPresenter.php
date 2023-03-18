<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.02.2017
 * Time: 15:01
 */

namespace App\Presenters;

use App\Forms\LostPassConfirmFormFactory;
use App\Forms\LostPassFormFactory;
use App\Forms\RegisterFormFactory;
use App\Model\Authenticator;
use App\Model\Emails\LostpassMail;
use App\Model\LogRepository;
use App\Model\UserManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\SendmailMailer;
use App\Model\Emails\RegistrationMail;
use App\Forms\BaseFormFactory;
use App\Forms\SignInFormFactory;

class AuthPresenter extends BasePresenter
{
    /** @persistent */
    public $backlink = '';

    /** @var SignInFormFactory @inject */
    public $signInFormFactory;

    /** @var RegisterFormFactory @inject */
    public $registerFormFactory;

    /** @var LostPassFormFactory @inject */
    public $lostPassFormFactory;

    /** @var LostPassConfirmFormFactory @inject */
    public $lostPassConfirmFormFactory;

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        $form = $this->signInFormFactory->create();

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);
        return $form;
    }

    public function signInFormSucceeded($form, $values)
    {
        $this->getUser()->setExpiration('1 hour', TRUE);

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->flashMessage('messages.user.login_successful', 'success');

            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_LOGIN);

            $this->restoreRequest($this->backlink);
            $this->redirect('Auth:');
        } catch (Nette\Security\AuthenticationException $e) {
            if($e->getCode() == Authenticator::INVALID_CREDENTIAL) { // log invalid attempt
                $user = $this->userManager->findOneByUsername($values->username);
                $this->logRepository->addLog($user, $this->current_ip, LogRepository::TYPE_AUTH_LOGIN_FAILED);
            }

            $this->flashMessage($e->getMessage(), 'danger');
        }
    }

    protected function createComponentRegisterForm()
    {
        $form = $this->registerFormFactory->create();

        $form->onSuccess[] = [$this, 'registerFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);
        return $form;
    }

    public function registerFormSucceeded(Form $form, $values)
    {
        $key = $this->userManager->add($values->username, $values->password, $values->email);
        $act_link = $this->generateUrlLink('activate', ['username' => $values->username, 'key' => $key]);

        // send mail
        $mail = new RegistrationMail($this->translator, $this->settingsCache, $values->username, $values->email, $act_link);
        $mailer = new SendmailMailer;
        $mailer->send($mail);

        // log
        $user = $this->userManager->findOneByUsername($values->username);
        if($user) {
            $this->logRepository->addLog(new Nette\Security\Identity($user->{UserManager::ACC_COLUMN_ID}), $this->current_ip, LogRepository::TYPE_AUTH_REGISTER);
        }

        $this->flashMessage('messages.register.successful_verify', 'success');
        $this->redirect('Auth:');
    }

    public function createComponentLostPassForm()
    {
        $form = $this->lostPassFormFactory->create();

        $form->onSuccess[] = [$this, 'lostPassFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);
        return $form;
    }

    public function lostPassFormSucceeded(Form $form, $values)
    {
        if($this->userManager->userAndEmailExists($values->username, $values->email)) {
            $key = $this->userManager->addPasswordRequest($values->username);
            $confirm_link = $this->generateUrlLink('confirm', ['username' => $values->username, 'key' => $key]);

            // send mail
            $mail = new LostpassMail($this->translator, $this->settingsCache, $values->username, $values->email, $confirm_link);
            $mailer = new SendmailMailer;
            $mailer->send($mail);

            // log
            $user = $this->userManager->findOneByUsername($values->username);
            if($user) {
                $this->logRepository->addLog(new Nette\Security\Identity($user->{UserManager::ACC_COLUMN_ID}), $this->current_ip, LogRepository::TYPE_AUTH_LOSTPASS);
            }

            $this->flashMessage('messages.lostpass.request_sent', 'success');
            $this->redirect('Auth:');
        }
        $this->flashMessage('messages.lostpass.request_failed', 'danger');
    }

    public function createComponentLostPassConfirmForm()
    {
        $form = $this->lostPassConfirmFormFactory->create();

        $form->onSuccess[] = [$this, 'lostPassConfirmFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);
        return $form;
    }

    public function lostPassConfirmFormSucceeded(Form $form, $values)
    {
        $username = $this->getParameter('username');
        $key = $this->getParameter('key');
        if(!$username || !$key) {
            $this->flashMessage('messages.global.something_wrong', 'danger');
            $this->redirect('lostpass');
        }

        if($this->userManager->changePasswordByRecovery($username, $values->password, $key)) {
            // log
            $user = $this->userManager->findOneByUsername($username);
            if($user) {
                $this->logRepository->addLog(new Nette\Security\Identity($user->{UserManager::ACC_COLUMN_ID}), $this->current_ip, LogRepository::TYPE_AUTH_LOSTPASS_CONFIRMED);
            }

            $this->flashMessage('messages.lostpass.passchange_success', 'success');
            $this->redirect('login');
        }
        $this->flashMessage('messages.lostpass.passchange_failed', 'danger');
    }

    /**
     * Login user
     */
    public function actionLogin()
    {
        if($this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:');
        }
    }

    /**
     * Logout user
     */
    public function actionLogout()
    {
        if($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();

            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_LOGOUT);

            $this->flashMessage('messages.user.logout_successful', 'success');
        }
        $this->redirect('login');
    }

    /**
     * Activate user's account
     *
     * @param $username
     * @param $key
     */
    public function actionActivate($username, $key)
    {
        if($this->userManager->activate($username, $key)) {
            $this->flashMessage('messages.register.activation_successful', 'success');
        } else {
            $this->flashMessage('messages.register.activation_failed', 'danger');
        }
        $this->redirect('login');
    }

    /**
     * Confirm request for password change
     *
     * @param $username
     * @param $key
     */
    public function actionConfirm($username, $key)
    {
        if(!$this->userManager->userAndPCKeyExists($username, $key)) {
            $this->flashMessage('messages.lostpass.invalid_key', 'danger');
            $this->redirect('lostpass');
        }
    }
}