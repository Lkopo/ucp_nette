<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.03.2017
 * Time: 19:41
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\ServiceSettingsFormFactory;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class ServiceSettingsPresenter extends BasePresenter
{
    /** @var ServiceSettingsFormFactory @inject */
    public $serviceSettingsFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentServiceSettingsForm()
    {
        $form = $this->serviceSettingsFormFactory->create();

        $form->onSuccess[] = [$this, 'serviceSettingsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form, false, true);

        return $form;
    }

    public function serviceSettingsFormSucceeded(Form $form, $values)
    {
        $realms = $this->settingsManager->getRealms();

        foreach ($realms as $realm) {
            $this->settingsManager->updateService(SettingsManager::SERVICE_RENAME, $realm->id, $values->{'rename_' . $realm->id});
            $this->settingsManager->updateService(SettingsManager::SERVICE_CUSTOMIZE, $realm->id, $values->{'customize_' . $realm->id});
            $this->settingsManager->updateService(SettingsManager::SERVICE_CHANGERACE, $realm->id, $values->{'change_race_' . $realm->id});
        }

        // purge cache
        $this->serviceSettingsCache->purge(SettingsManager::SERVICE_RENAME);
        $this->serviceSettingsCache->purge(SettingsManager::SERVICE_CUSTOMIZE);
        $this->serviceSettingsCache->purge(SettingsManager::SERVICE_CHANGERACE);

        $this->flashMessage('messages.service_settings.successfuly_changed', 'success');
    }
}