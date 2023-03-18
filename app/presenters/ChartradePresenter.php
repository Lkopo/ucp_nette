<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 04.03.2017
 * Time: 12:38
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\MakeOfferConfirmFormFactory;
use App\Model\AccountRepository;
use App\Model\CharacterRepository;
use App\Model\ChartradeRepository;
use App\Model\Emails\ChartradeMail;
use App\Model\LogRepository;
use App\Forms\MakeOfferFormFactory;
use App\Model\SettingsManager;
use App\Model\UserManager;
use Nette\Mail\SendmailMailer;

class ChartradePresenter extends BasePresenter
{
    /** @var MakeOfferFormFactory @inject */
    public $makeOfferFormFactory;

    /** @var MakeOfferConfirmFormFactory @inject */
    public $makeOfferConfirmFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkUserBanned();
        $this->checkModuleEnabled(SettingsManager::MODULE_CHARTRADE);

        // check if user has higher role than player, if yes, redirect to the homepage with
        // access denied error
        if($this->roleCache->getLevel($this->user->roles[0]) > AccountRepository::ROLE_PLAYER) {
            $this->flashMessage('messages.user.access_denied', 'danger');
            $this->redirect('Homepage:');
        }
    }

    public function createComponentMakeOfferForm()
    {
        $form = $this->makeOfferFormFactory->create();

        $form->onSubmit[] = [$this, 'updateCharacterSnippet'];
        $form->onSuccess[] = [$this, 'makeOfferFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function makeOfferFormSucceeded($form, $values)
    {
        $dest_character = $this->characterRepository->findOneByName($values->dest_character);

        $this->setView('previewOffer');
        $this->template->dest_character = $dest_character;
    }

    public function createComponentMakeOfferConfirmForm()
    {
        $my_character = $this->getHttpRequest()->getPost('my_character');
        $dest_character = $this->getHttpRequest()->getPost('dest_character');

        $form = $this->makeOfferConfirmFormFactory->create($my_character, $dest_character);

        $form->onError[] = [$this, 'makeOfferConfirmFormFailed'];
        $form->onSuccess[] = [$this, 'makeOfferConfirmFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function makeOfferConfirmFormFailed($form)
    {
        $this->flashMessage('forms.chartrade.confirm_error', 'danger');
        $this->redirect('makeOffer');
    }

    public function makeOfferConfirmFormSucceeded($form, $values)
    {
        // if cancelled
        if($form['cancel']->isSubmittedBy())
            $this->redirect('makeOffer');

        $offerer = $this->characterRepository->findOneById($values->my_character);
        $offerer_ip = $this->current_ip;
        $requested = $this->characterRepository->findOneByName($values->dest_character);
        $requested_ip = '';

        $key = $this->chartradeRepository->preMakeOffer($offerer, $offerer_ip, $requested, $requested_ip);

        $trade = $this->chartradeRepository->findOneByKey($key);
        if(!$trade) {
            $this->flashMessage('messages.global.something_wrong', 'danger');
            $this->redirect('default');
        }

        // send mail
        $confirm_link = $this->generateUrlLink('verify', ['key' => $key]);

        $mail = new ChartradeMail($this->translator, $this->settingsCache, $this->user->identity->{UserManager::ACC_COLUMN_NAME}, $this->user->identity->{UserManager::ACC_COLUMN_EMAIL}, $trade->{ChartradeRepository::COLUMN_OFFERER_NAME}, $trade->{ChartradeRepository::COLUMN_REQUESTED_NAME}, $confirm_link, ChartradeMail::TYPE_VERIFY_OFFERER);
        $mailer = new SendmailMailer;
        $mailer->send($mail);

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_TRADE_OFFER_CREATED, $trade->{ChartradeRepository::COLUMN_ID});

        $this->flashMessage('messages.chartrade.offer_premade', 'success');
    }

    public function updateCharacterSnippet($form)
    {
        $guid = $form->values->my_character;

        if((int) $guid == 0) {
            $this->template->character = NULL;
            return;
        }

        $character = $this->characterRepository->findOneById($guid);

        $this->template->character = $character;
    }

    public function handleSelected($guid)
    {
        if(!$this->isAjax())
            return;

        if((int) $guid == 0) {
            $this->template->character = NULL;
            $this->redrawControl('characterContainer');
            return;
        }

        $character = $this->characterRepository->findOneById($guid);

        // if someone tries to select character which does not belong
        // to his account
        if($character->{CharacterRepository::C_COLUMN_ACC_ID} != $this->user->id)
            $character = NULL;

        $this->template->character = $character;

        $this->redrawControl('characterContainer');
    }

    /** @secured */
    public function handleCancel($id)
    {
        if(!$this->isAjax())
            return;

        if($id == NULL) {
            $this->flashMessage('messages.global.invalid_request', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $trade = $this->chartradeRepository->findOneById($id);

        // if trade does not exist or user does not take any role for this trade
        if(!$trade || ($trade->requested_account != $this->user->id && $trade->offerer_account != $this->user->id)) {
            $this->flashMessage('messages.global.invalid_request', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // change trade status
        $this->chartradeRepository->cancelTrade($trade);

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_TRADE_OFFER_CANCELLED, $trade->{ChartradeRepository::COLUMN_ID});

        $this->flashMessage('messages.chartrade.cancelled', 'success');
        $this->redirect('MyCharacters:');
    }

    /** @secured */
    public function handleAccept($id)
    {
        if(!$this->isAjax())
            return;

        if($id == NULL) {
            $this->flashMessage('messages.global.invalid_request', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $trade = $this->chartradeRepository->findOneById($id);

        // if trade does not exist or user does not take any role for this trade
        if(!$trade || ($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC} != $this->user->id && $trade->{ChartradeRepository::COLUMN_OFFERER_ACC} != $this->user->id)) {
            $this->flashMessage('messages.global.invalid_request', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // only requested can accept offer
        if($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC} != $this->user->id) {
            $this->flashMessage('messages.chartrade.cannot_accept_by_offerer', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $offerer = $this->characterRepository->findOneById($trade->{ChartradeRepository::COLUMN_OFFERER});
        $requested = $this->characterRepository->findOneById($trade->{ChartradeRepository::COLUMN_REQUESTED});

        // do their characters still exist?
        if(!$offerer || !$requested) {
            $this->flashMessage('messages.chartrade.does_not_exist', 'danger');
            $this->chartradeRepository->cancelTrade($trade); // cancel trade
            $this->redirect('default');
        }

        // is requested privileged?
        if($this->userManager->getGmLevel($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC}) > 0 || $this->accountRepository->findAllUserRolesById($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC})->count() > 0) {
            $this->flashMessage('forms.chartrade.dest_privileged', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // has requested acc ban?
        if($this->userManager->hasBan($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC})) {
            $this->flashMessage('forms.chartrade.banned_dest_acc', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // has offerer mute?
        if($this->userManager->hasMute($trade->{ChartradeRepository::COLUMN_OFFERER_ACC})) {
            $this->flashMessage('forms.chartrade.muted', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // has requested mute?
        if($this->userManager->hasMute($trade->{ChartradeRepository::COLUMN_REQUESTED_ACC})) {
            $this->flashMessage('forms.chartrade.muted_dest', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // has offerer char ban?
        if($this->characterRepository->hasBan($offerer->{CharacterRepository::C_COLUMN_ID})) {
            $this->flashMessage('forms.chartrade.select_banned', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // has requested char ban?
        if($this->characterRepository->hasBan($requested->{CharacterRepository::C_COLUMN_ID})) {
            $this->flashMessage('forms.chartrade.dest_banned', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // are they both offline?
        if($offerer->{CharacterRepository::C_COLUMN_ONLINE} == CharacterRepository::STATUS_ONLINE || $requested->{CharacterRepository::C_COLUMN_ONLINE} == CharacterRepository::STATUS_ONLINE) {
            $this->flashMessage('messages.chartrade.someone_online', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // is verified?
        if($trade->{ChartradeRepository::COLUMN_VERIFY_TYPE} != 0) {
            $this->flashMessage('messages.chartrade.not_verified', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // finally accept trade
        $this->chartradeRepository->makeTrade($trade);

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_TRADE_OFFER_ACCEPTED, $trade->{ChartradeRepository::COLUMN_ID});

        $this->flashMessage('messages.chartrade.accepted', 'success');
        $this->redirect('MyCharacters:');
    }

    public function renderDefault()
    {
        // check if user has some characters, if not, redirect to the character list
        if($this->characterRepository->findByAccount($this->user->id)->count() == 0)
            $this->redirect('MyCharacters:');

        // redirect if no offers/requests to show
        $trades = $this->chartradeRepository->findVerifiedByAccount($this->user->id);
        if($trades->count() == 0)
            $this->redirect('makeOffer');

        $this->template->characterRepository = $this->characterRepository;
        $this->template->trades = $trades;
    }

    public function actionVerify($key)
    {
        $trade = $this->chartradeRepository->findOneByKey($key);

        if(!$trade) {
            $this->flashMessage('messages.chartrade.invalid_key', 'danger');
            $this->redirect('default');
        }

        switch($trade->verify_type) {
            case 1: // premade offer
                // check ownership of offerer
                if(!$this->chartradeRepository->findOneByOffered($this->user->id)) {
                    $this->flashMessage('messages.chartrade.invalid_person', 'danger');
                    $this->redirect('default');
                }

                // check if does not havy any chartrade offer already verified
                if($this->chartradeRepository->findVerifiedByAccount($this->user->id)->count() > 0) {
                    $this->flashMessage('messsages.chatrade.verify_already_offer', 'danger');
                    $this->redirect('default');
                }

                $key = $this->chartradeRepository->verifyPreMade($trade);
                $confirm_link = $this->generateUrlLink('verify', ['key' => $key]);

                // send mail
                $mail = new ChartradeMail($this->translator, $this->settingsCache, $this->user->identity->{UserManager::ACC_COLUMN_NAME}, $this->user->identity->{UserManager::ACC_COLUMN_EMAIL}, $trade->{ChartradeRepository::COLUMN_OFFERER_NAME}, $trade->{ChartradeRepository::COLUMN_REQUESTED_NAME}, $confirm_link, ChartradeMail::TYPE_VERIFY_REQUESTED);
                $mailer = new SendmailMailer;
                $mailer->send($mail);

                // log
                $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_TRADE_OFFER_VERIFIED_OFFERER, $trade->{ChartradeRepository::COLUMN_ID});

                $this->flashMessage('messages.chartrade.premade_verified', 'success');
                break;
            case 2: // verify by requested
                // check ownership of requested
                if(!$this->chartradeRepository->findOneByRequested($this->user->id)) {
                    $this->flashMessage('messages.chartrade.invalid_person', 'danger');
                    $this->redirect('default');
                }

                $this->chartradeRepository->verifyOffer($trade);

                // log
                $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_TRADE_OFFER_VERIFIED_REQUESTED, $trade->{ChartradeRepository::COLUMN_ID});

                $this->flashMessage('messages.chartrade.offer_verified', 'success');
                break;
            default:
                $this->flashMessage('messages.global.something_wrong', 'danger');
        }

        $this->redirect('default');
    }

    /**
     * used action instead of render because of render is called after handle
     * so template's $character variable will be always NULL even after AJAX
     * call
     */
    public function actionMakeOffer()
    {
        // check if user has some characters, if not, redirect to the character list
        if($this->characterRepository->findByAccount($this->user->id)->count() == 0)
            $this->redirect('MyCharacters:');

        // if has an offer/request to trade, redirect to the list
        if($this->chartradeRepository->findVerifiedByAccount($this->user->id)->count() != 0)
            $this->redirect('default');

        $this->template->character = NULL;
    }
}