<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 14:11
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\SelectCharacterFormFactory;
use App\Model\CharacterRepository;
use App\Model\LogRepository;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class MyCharactersPresenter extends BasePresenter
{
    /** @var SelectCharacterFormFactory @inject */
    public $selectCharacterFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentRenameCharacterForm()
    {
        $form = $this->selectCharacterFormFactory->create();

        $form->onValidate[] = [$this, 'checkPriceForRename'];
        $form->onSubmit[] = [$this, 'updateCharacterSnippet'];
        $form->onSuccess[] = [$this, 'renameCharacterFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function createComponentCustomizeCharacterForm()
    {
        $form = $this->selectCharacterFormFactory->create();

        $form->onValidate[] = [$this, 'checkPriceForCustomize'];
        $form->onSubmit[] = [$this, 'updateCharacterSnippet'];
        $form->onSuccess[] = [$this, 'customizeCharacterFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function createComponentChangeRaceForm()
    {
        $form = $this->selectCharacterFormFactory->create();

        $form->onValidate[] = [$this, 'checkPriceForChangeRace'];
        $form->onSubmit[] = [$this, 'updateCharacterSnippet'];
        $form->onSuccess[] = [$this, 'changeRaceFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function checkPriceForRename(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character) {
            $this->flashMessage('forms.global_character.not_exists', 'danger');
            return false;
        }

        if($character->{CharacterRepository::C_COLUMN_MONEY} * 10000 < $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_RENAME, $character->{CharacterRepository::C_COLUMN_REALM})) {
            $this->flashMessage('forms.global_character.not_enough_money', 'danger');
            return false;
        }

        return true;
    }

    public function checkPriceForCustomize(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character) {
            $this->flashMessage('forms.global_character.not_exists', 'danger');
            return false;
        }

        if($character->{CharacterRepository::C_COLUMN_MONEY} * 10000 < $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_CUSTOMIZE, $character->{CharacterRepository::C_COLUMN_REALM})) {
            $this->flashMessage('forms.global_character.not_enough_money', 'danger');
            return false;
        }

        return true;
    }

    public function checkPriceForChangerace(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character) {
            $this->flashMessage('forms.global_character.not_exists', 'danger');
            return false;
        }

        if($character->{CharacterRepository::C_COLUMN_MONEY} * 10000 < $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_CHANGERACE, $character->{CharacterRepository::C_COLUMN_REALM})) {
            $this->flashMessage('forms.global_character.not_enough_money', 'danger');
            return false;
        }

        return true;
    }

    public function updateCharacterSnippet(Form $form)
    {
        $guid = $form->values->my_character;

        if((int) $guid == 0) {
            $this->template->character = NULL;
            return;
        }

        $character = $this->characterRepository->findOneById($guid);

        $this->template->character = $character;
    }

    public function renameCharacterFormSucceeded(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character)
            return;

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_CHAR_RENAME, $character->{CharacterRepository::C_COLUMN_ID} . '-' . $character->{CharacterRepository::C_COLUMN_NAME});

        $this->characterRepository->rename($character, $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_RENAME, $character->{CharacterRepository::C_COLUMN_REALM}));

        $this->flashMessage('messages.global_character.rename_successful', 'success');
        $this->redirect('MyCharacters:');
    }

    public function customizeCharacterFormSucceeded(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character)
            return;

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_CHAR_CUSTOMIZE, $character->{CharacterRepository::C_COLUMN_ID});

        $this->characterRepository->customize($character, $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_CUSTOMIZE, $character->{CharacterRepository::C_COLUMN_REALM}));

        $this->flashMessage('messages.global_character.customize_successful', 'success');
        $this->redirect('MyCharacters:');
    }

    public function changeRaceFormSucceeded(Form $form, $values)
    {
        $character = $this->characterRepository->findOneById($values->my_character);

        if(!$character)
            return;

        // log
        $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_CHAR_CHANGERACE, $character->{CharacterRepository::C_COLUMN_ID});

        $this->characterRepository->changeRace($character, $this->serviceSettingsCache->getPrice(SettingsManager::SERVICE_CHANGERACE, $character->{CharacterRepository::C_COLUMN_REALM}));

        $this->flashMessage('messages.global_character.changerace_successful', 'success');
        $this->redirect('MyCharacters:');
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

    public function renderDefault()
    {
        // user must be logged in to view his characters
        $this->checkUserLoggedIn();

        // get my characters
        $characters = $this->characterRepository->findByAccount($this->user->id);

        //Debugger::dump($characters);

        $this->template->characters = $characters;
    }

    public function actionRename()
    {
        $this->checkUserBanned(); // banned players cannot rename their characters
        $this->checkModuleEnabled(SettingsManager::MODULE_RENAME); // check if module is enabled

        $this->template->character = NULL;
    }

    public function actionCustomize()
    {
        $this->checkUserBanned(); // banned players cannot customize their characters
        $this->checkModuleEnabled(SettingsManager::MODULE_CUSTOMIZE); // check if module is enabled

        $this->template->character = NULL;
    }

    public function actionChangeRace()
    {
        $this->checkUserBanned(); // banned players cannot change race of their characters
        $this->checkModuleEnabled(SettingsManager::MODULE_CHANGERACE); // check if module is enabled

        $this->template->character = NULL;
    }
}