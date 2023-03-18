<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 18:34
 */

namespace App\Forms;

use App\Caching\SettingsCache;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class PageSettingsFormFactory extends BaseFormFactory
{
    /** @var SettingsCache */
    private $settingsCache;

    public function __construct(SettingsCache $settingsCache)
    {
        $this->settingsCache = $settingsCache;
    }

    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $settings = $this->settingsCache->getAll();

        $form->addGroup();
        $form->addText('page_name', 'forms.page_settings.page_name')
            ->setDefaultValue($settings->{SettingsManager::SETT_COLUMN_PAGE_NAME});

        $form->addText('page_description', 'forms.page_settings.page_description')
            ->setDefaultValue($settings->{SettingsManager::SETT_COLUMN_PAGE_DESC});

        $form->addText('page_keywords', 'forms.page_settings.page_keywords')
            ->setDefaultValue($settings->{SettingsManager::SETT_COLUMN_PAGE_KEYWORDS});

        $form->addText('page_email', 'forms.page_settings.page_email')
            ->setDefaultValue($settings->{SettingsManager::SETT_COLUMN_PAGE_EMAIL})
            ->setRequired('forms.page_settings.page_email_empty')
            ->addRule(Form::EMAIL, 'forms.register.email_invalid');

        $form->addText('page_email_sign', 'forms.page_settings.page_email_sign')
            ->setDefaultValue($settings->{SettingsManager::SETT_COLUMN_PAGE_EMAIL_SIGN});

        $form->addGroup('');

        $form->addSubmit('submit', 'forms.page_settings.submit')
            ->setAttribute('class', 'btn-lg btn-success btn-block');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}