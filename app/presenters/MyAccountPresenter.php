<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 05.03.2017
 * Time: 16:24
 */

namespace App\Presenters;

use App\Model\AccountRepository;
use App\Forms\LostPassConfirmFormFactory;
use App\Forms\BaseFormFactory;
use App\Model\Emails\LockUnlockMail;
use App\Model\Emails\PasswordChangeMail;
use App\Model\LogRepository;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Mail\SendmailMailer;

class MyAccountPresenter extends BasePresenter
{
    /** @var LostPassConfirmFormFactory @inject */
    public $lostPassConfirmFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentChangePassForm()
    {
        // use lost password confirm form
        $form = $this->lostPassConfirmFormFactory->create();

        $form->onSuccess[] = [$this, 'changePassFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);
        return $form;
    }

    public function changePassFormSucceeded(Form $form, $values)
    {
        $key = $this->getParameter('key');
        if(!$key) {
            $this->flashMessage('messages.global.something_wrong', 'danger');
            $this->redirect('default');
        }

        if($this->accountRepository->changePassword($this->user->id, $values->password, $key)) {
            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_CHANGEPASS_CONFIRMED);

            $this->flashMessage('messages.my_account.passchange.success', 'success');
            $this->redirect('default');
        }
        $this->flashMessage('messages.my_account.passchange.failed', 'danger');
    }

    /** @secured */
    public function handlePasswordRequest()
    {
        // wait at least 15 minutes before new request is going to be sent
        $pc_request = $this->accountRepository->findOnePCRequestById($this->user->id);
        if($pc_request && (time() - $pc_request->{AccountRepository::PC_COLUMN_TIME} < 60 * 15)) {
            $this->flashMessage('messages.my_account.pc_request_already_sent', 'danger');
        } else {
            $key = $this->accountRepository->addPCRequest($this->user);
            $confirm_link = $this->generateUrlLink('changePassword', ['key' => $key]);

            // send mail
            $mail = new PasswordChangeMail($this->translator, $this->settingsCache, $this->user->identity->{UserManager::ACC_COLUMN_NAME}, $this->user->identity->{UserManager::ACC_COLUMN_EMAIL}, $confirm_link);
            $mailer = new SendmailMailer;
            $mailer->send($mail);

            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_CHANGEPASS);

            $this->flashMessage('messages.my_account.pc_request_sent', 'success');
        }
        $this->redrawControl('flashes');
    }

    /** @secured */
    public function handleLockUnlockRequest()
    {
        // wait at least 15 minutes before new request is going to be sent
        $lock_request = $this->userManager->findOneLockRequestById($this->user->id);
        if($lock_request && (time() - $lock_request->{UserManager::LOCK_COLUMN_TIME} < 60 * 15)) {
            $this->flashMessage('messages.my_account.lock_request_already_sent', 'danger');
        } else {
            $key = $this->userManager->addLockUnlockRequest($this->user);
            $confirm_link = $this->generateUrlLink('lockUnlock', ['key' => $key]);

            // send mail
            $mail = new LockUnlockMail($this->translator, $this->settingsCache, $this->user->identity->{UserManager::ACC_COLUMN_NAME}, $this->user->identity->{UserManager::ACC_COLUMN_EMAIL}, $confirm_link, ($this->user->identity->{UserManager::ACC_COLUMN_LOCKED} == UserManager::STATUS_LOCKED ? LockUnlockMail::TYPE_UNLOCK : LockUnlockMail::TYPE_LOCK));
            $mailer = new SendmailMailer;
            $mailer->send($mail);

            $this->flashMessage('messages.my_account.lock_request_sent', 'success');
        }
        $this->redrawControl('flashes');
    }

    public function renderChangePassword($key)
    {
        $request = $this->accountRepository->findOnePCRequestByIdAndKey($this->user->id, $key);

        // if no match
        if(!$request) {
            $this->flashMessage('messages.my_account.invalid_key', 'danger');
            $this->redirect('default');
        }
    }

    public function actionLockUnlock($key)
    {
        $request = $this->userManager->findOneLockRequestByIdAndKey($this->user->id, $key);

        // if no match
        if(!$request) {
            $this->flashMessage('messages.my_account.invalid_key', 'danger');
            $this->redirect('default');
        }

        $this->userManager->lockUnlockUser($this->user, $request);

        if($request->{UserManager::LOCK_COLUMN_TYPE} == UserManager::STATUS_LOCKED) {
            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_ACC_LOCK);

            $this->flashMessage('messages.my_account.locked', 'success');
        } else {
            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_AUTH_ACC_UNLOCK);

            $this->flashMessage('messages.my_account.unlocked', 'success');
        }

        $this->redirect('default');
    }
}