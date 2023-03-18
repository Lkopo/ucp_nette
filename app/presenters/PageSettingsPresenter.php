<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 18:39
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\PageSettingsFormFactory;
use Nette\Application\UI\Form;

class PageSettingsPresenter extends BasePresenter
{
    /** @var PageSettingsFormFactory @inject */
    public $pageSettingsFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentPageSettingsForm()
    {
        $form = $this->pageSettingsFormFactory->create();

        $form->onSuccess[] = [$this, 'pageSettingsFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function pageSettingsFormSucceeded(Form $form, $values)
    {
        $this->settingsManager->changeSettings($values->page_name, $values->page_description, $values->page_keywords, $values->page_email, $values->page_email_sign);

        // purge cache
        $this->settingsCache->purge();

        $this->flashMessage('messages.page_settings.successfuly_changed', 'success');
    }
}