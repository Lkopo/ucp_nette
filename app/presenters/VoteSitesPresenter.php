<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 20.08.2017
 * Time: 12:25
 */

namespace App\Presenters;

use App\Forms\BaseFormFactory;
use App\Forms\VoteSitesFormFactory;
use App\Model\SettingsManager;
use Nette\Application\UI\Form;

class VoteSitesPresenter extends BasePresenter
{
    /** @var VoteSitesFormFactory @inject */
    public $voteSitesFormFactory;

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_VOTE);
    }

    public function createComponentVoteSiteForm()
    {
        $id = $this->getParameter('id');
        if($id)
            $form = $this->voteSitesFormFactory->create(TRUE);
        else
            $form = $this->voteSitesFormFactory->create();

        $form->onSuccess[] = [$this, 'voteSiteFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function voteSiteFormSucceeded(Form $form, $values)
    {
        $id = $this->getParameter('id');
        if($id) {
            if($this->voteManager->updateVoteSite($id, $values->name, $values->url, $values->check_url, $values->image_path, $values->image_width, $values->image_height, $values->points, $values->cooldown, $values->script_id)) {
                $this->flashMessage('messages.vote_sites.successfuly_changed', 'success');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        } else {
            if ($this->voteManager->addVoteSite($values->name, $values->url, $values->check_url, $values->image_path, $values->image_width, $values->image_height, $values->points, $values->cooldown, $values->script_id)) {
                $this->flashMessage('messages.vote_sites.successfuly_added', 'success');
                $this->redirect('default');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        }
    }

    /** @secured */
    public function handleDelete($site_id)
    {
        if(!$this->isAjax())
            return;

        $site = $this->voteManager->findOneSiteById($site_id);

        if(!$site) {
            $this->flashMessage('messages.vote_site.site_notfound', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $site->delete();

        $this->flashMessage('messages.vote_sites.successfuly_deleted', 'success');
        $this->redrawControl('flashes');
        $this->redrawControl('voteSitesList');
    }

    public function renderEdit($id)
    {
        $site = $this->voteManager->findOneSiteById($id);

        if(!$site) {
            $this->flashMessage('messages.vote_site.site_notfound', 'danger');
            $this->redirect('default');
        }

        $this['voteSiteForm']->setDefaults($site->toArray());
    }

    public function renderDefault()
    {
        $this->template->vote_sites = $this->voteManager->findAllSites();
    }
}