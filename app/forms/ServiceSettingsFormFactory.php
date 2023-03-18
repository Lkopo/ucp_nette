<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 26.03.2017
 * Time: 19:42
 */

namespace App\Forms;

use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class ServiceSettingsFormFactory extends BaseFormFactory
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

        $realms = $this->settingsManager->getRealms();

        $form->addGroup('forms.service_settings.rename');
        foreach ($realms as $realm) {
            $service = $this->settingsManager->getServiceSettingByNameAndRealm(SettingsManager::SERVICE_RENAME, $realm->id);
            $form->addText('rename_' . $realm->{SettingsManager::RLMS_COLUMN_ID})
                ->setOption('description', $realm->{SettingsManager::RLMS_COLUMN_NAME} . ' Realm')
                ->setTranslator(NULL)
                ->setRequired('forms.service_settings.price_empty')
                ->setDefaultValue($service->{SettingsManager::SRVC_COLUMN_PRICE})
                ->addRule(Form::MIN, 'forms.service_settings.price_lower_than_zero', 0)
                ->addRule(Form::INTEGER, 'forms.service_settings.price_not_numeric')
                ->setAttribute('min', 0)
                ->getLabelPrototype()->setName(NULL);
        }

        $form->addGroup('forms.service_settings.customize');
        foreach ($realms as $realm) {
            $service = $this->settingsManager->getServiceSettingByNameAndRealm(SettingsManager::SERVICE_CUSTOMIZE, $realm->id);
            $form->addText('customize_' . $realm->{SettingsManager::RLMS_COLUMN_ID})
                ->setOption('description', $realm->{SettingsManager::RLMS_COLUMN_NAME} . ' Realm')
                ->setTranslator(NULL)
                ->setRequired('forms.service_settings.price_empty')
                ->setDefaultValue($service->{SettingsManager::SRVC_COLUMN_PRICE})
                ->addRule(Form::MIN, 'forms.service_settings.price_lower_than_zero', 0)
                ->addRule(Form::INTEGER, 'forms.service_settings.price_not_numeric')
                ->setAttribute('min', 0)
                ->getLabelPrototype()->setName(NULL);
        }

        $form->addGroup('forms.service_settings.change_race');
        foreach ($realms as $realm) {
            $service = $this->settingsManager->getServiceSettingByNameAndRealm(SettingsManager::SERVICE_CHANGERACE, $realm->id);
            $form->addText('change_race_' . $realm->{SettingsManager::RLMS_COLUMN_ID})
                ->setOption('description', $realm->{SettingsManager::RLMS_COLUMN_NAME} . ' Realm')
                ->setTranslator(NULL)
                ->setRequired('forms.service_settings.price_empty')
                ->setDefaultValue($service->{SettingsManager::SRVC_COLUMN_PRICE})
                ->addRule(Form::MIN, 'forms.service_settings.price_lower_than_zero', 0)
                ->addRule(Form::INTEGER, 'forms.service_settings.price_not_numeric')
                ->setAttribute('min', 0)
                ->getLabelPrototype()->setName(NULL);
        }


        $form->addGroup('');
        $form->addSubmit('submit', 'forms.service_settings.submit')
            ->setAttribute('class', 'btn-lg btn-success');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}