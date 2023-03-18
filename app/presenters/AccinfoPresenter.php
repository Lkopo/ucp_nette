<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.03.2017
 * Time: 21:05
 */

namespace App\Presenters;

use App\Forms\AccinfoSearchFormFactory;
use App\Forms\AccSearchByEmailFormFactory;
use App\Forms\AccSearchByIpFormFactory;
use App\Forms\BaseFormFactory;
use App\Model\Emails\DeactivationMail;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Mail\SendmailMailer;

class AccinfoPresenter extends BasePresenter
{
    /** @var AccinfoSearchFormFactory @inject */
    public $accinfoSearchFormFactory;

    /** @var AccSearchByEmailFormFactory @inject */
    public $accSearchByEmailFormFactory;

    /** @var AccSearchByIpFormFactory @inject */
    public $accSearchByIpFormFactory;

    /** @persistent */
    public $acc;

    /** @persistent */
    public $email;

    /** @persistent */
    public $ip;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentAccinfoSearchForm()
    {
        $form = $this->accinfoSearchFormFactory->create();

        $form->setAction($this->link('default', ['acc' => null, 'email' => null, 'ip' => null]));

        $form['username_id']->setDefaultValue($this->getParameter('acc'));

        $form->onSuccess[] = [$this, 'accinfoSearchFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function createComponentAccSearchByEmailForm()
    {
        $form = $this->accSearchByEmailFormFactory->create();

        $form->setAction($this->link('default', ['acc' => null, 'email' => null, 'ip' => null]));

        $form['email']->setDefaultValue($this->getParameter('email'));

        $form->onSuccess[] = [$this, 'accSearchByEmailFormSucceded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function createComponentAccSearchByIpForm()
    {
        $form = $this->accSearchByIpFormFactory->create();

        $form->setAction($this->link('default', ['acc' => null, 'email' => null, 'ip' => null]));

        $form['ip']->setDefaultValue($this->getParameter('ip'));

        $form->onSuccess[] = [$this, 'accSearchByIpFormSucceded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function accinfoSearchFormSucceeded(Form $form, $values)
    {
        $this->redirect('this', ['acc' => (empty($values->username_id) ? null : $values->username_id)]);
    }

    public function accSearchByEmailFormSucceded(Form $form, $values)
    {
        $this->redirect('this', ['email' => (empty($values->email) ? null : $values->email)]);
    }

    public function accSearchByIpFormSucceded(Form $form, $values)
    {
        $this->redirect('this', ['ip' => (empty($values->ip) ? null : $values->ip)]);
    }

    /** @secured */
    public function handleDeactivate($acc_id)
    {
        $account = $this->userManager->findOneById($acc_id);

        if(!$account) {
            $this->flashMessage('messages.accinfo.user_notfound', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $key = $this->userManager->deactivate($acc_id);
        $act_link = $this->generateUrlLink('activate', ['username' => $account->{UserManager::ACC_COLUMN_NAME}, 'key' => $key]);

        // send mail
        $mail = new DeactivationMail($this->translator, $this->settingsCache, $account->{UserManager::ACC_COLUMN_NAME}, $account->{UserManager::ACC_COLUMN_EMAIL}, $act_link);
        $mailer = new SendmailMailer;
        $mailer->send($mail);

        $this->template->account = $account;

        $this->flashMessage('messages.accinfo.successfuly_deactivated', 'success');
        $this->redrawControl('flashes');
        $this->redrawControl('accountInfo');
    }

    public function renderDefault()
    {
        $acc = $this->getParameter('acc');
        $email = $this->getParameter('email');
        $ip = $this->getParameter('ip');

        $this->template->account = null;

        if($acc) {
            $account = $this->userManager->findOneByUsernameOrId($acc);
            if($account) {
                $this->template->account = $account;
                $this->template->bans = $this->userManager->findAllBansForId($account->id);
                //$this->template->donationStats = $this->donateManager->getDonateStatsForAccount($account);
                //$this->template->donates = $this->donateManager->findAllDonatesForAccount($account);
            } else {
                $this->flashMessage('messages.accinfo.username_id_notfound', 'danger');
                $this->redirect('this', ['acc' => null]);
            }
        } else if($email) {
            $accounts = $this->userManager->findAllByEmail($email);

            $this->setView('list');
            $this->template->accounts = $accounts;
            $this->template->search_by = 'email';
        } else if($ip) {
            $accounts = $this->userManager->findAllByIp($ip);

            $this->setView('list');
            $this->template->accounts = $accounts;
            $this->template->search_by = 'ip';
        }
    }
}