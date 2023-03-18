<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 18.08.2017
 * Time: 14:41
 */

namespace App\Forms;

use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class ModuleSettingsFormFactory extends BaseFormFactory
{
    /** @var SettingsManager */
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        $this->settingsManager = $settingsManager;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $t = $this->translator;

        $modules = $this->settingsManager->getStatusModules();

        foreach ($modules as $module) {
            $form->addGroup('forms.module_settings.' . $module->{SettingsManager::MODULES_COLUMN_NAME});

            $form->addCheckbox($module->{SettingsManager::MODULES_COLUMN_NAME})
                ->setAttribute('data-toggle', 'toggle')
                ->setAttribute('data-on', $t->trans('forms.global.enabled'))
                ->setAttribute('data-off', $t->trans('forms.global.disabled'))
                ->setAttribute('data-onstyle', 'success')
                ->setAttribute('data-offstyle', 'danger')
                ->setAttribute('class', 'left-checkbox')
                ->setDefaultValue($module->{SettingsManager::MODULES_COLUMN_VALUE})
                ->getLabelPrototype()->setName(NULL);
        }

        $form->addGroup('');
        $form->addSubmit('submit', 'forms.module_settings.submit')
            ->setAttribute('class', 'btn-lg btn-success');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}