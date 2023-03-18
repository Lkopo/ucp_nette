<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 18.08.2017
 * Time: 14:56
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\ModuleSettingsFormFactory;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class ModuleSettingsPresenter extends BasePresenter
{
    /** @var ModuleSettingsFormFactory @inject */
    public $moduleSettingsFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentModuleSettingsForm()
    {
        $form = $this->moduleSettingsFormFactory->create();

        $form->onSuccess[] = [$this, 'moduleSettingsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function moduleSettingsFormSucceeded(Form $form, $values)
    {
        // only status type is supported now
        foreach ($values as $key => $value) {
            $this->settingsManager->updateModule($key, SettingsManager::MODULE_TYPE_STATUS, $value);
            $this->moduleSettingsCache->purge($key); // purge cache
        }

        $this->flashMessage('messages.module_settings.successfuly_changed', 'success');
    }
}