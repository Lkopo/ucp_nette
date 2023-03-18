<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.08.2017
 * Time: 13:26
 */

namespace App\Presenters;

use App\Model\LogRepository;
use App\Model\SettingsManager;
use App\Model\VoteManager;

class VotePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_VOTE);
    }

    private function makeVote($site_id)
    {
        // get in count
        if(($in_count = $this->voteManager->getInCount($site_id)) === false) {
            $this->flashMessage('messages.vote.internal_fail', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // set account to voting state
        $this->voteManager->startVote($this->user, $site_id, $in_count);
    }

    public function handleClicked($site_id)
    {
        if(!$this->isAjax())
            return;

        $user_vote = $this->voteManager->findOneUserVoteByUserIdAndSiteId($this->user->id, $site_id);

        $site = $this->voteManager->findOneSiteById($site_id);

        if(!$site) {
            $this->flashMessage('messages.vote.internal_fail', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        if($user_vote && $user_vote->{VoteManager::VOTES_COLUMN_STATE} == VoteManager::STATE_VOTED && $user_vote->{VoteManager::VOTES_COLUMN_TIME}+$site->{VoteManager::SITE_COLUMN_COOLDOWN}*VoteManager::VOTE_MULTIPLY_HOURS >= time()) {
            $this->flashMessage('messages.vote.already_voted', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        $this->makeVote($site_id);

        $sites = $this->voteManager->findAllSites();

        $this->template->sites = $sites;
        $this->redrawControl('siteList');
    }

    /** @secured */
    public function handleConfirmed($site_id)
    {
        if(!$this->isAjax())
            return;

        $user_vote = $this->voteManager->findOneUserVoteByUserIdAndSiteId($this->user->id, $site_id);
        $site = $this->voteManager->findOneSiteById($site_id);

        if(!$user_vote || !$site) {
            $this->flashMessage('messages.vote.invalid_confirmation', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        if($user_vote->{VoteManager::VOTES_COLUMN_STATE} == VoteManager::STATE_VOTED && $user_vote->{VoteManager::VOTES_COLUMN_TIME}+$site->{VoteManager::SITE_COLUMN_COOLDOWN}*VoteManager::VOTE_MULTIPLY_HOURS >= time()) {
            $this->flashMessage('messages.vote.already_voted', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        if(($in_count = $this->voteManager->getInCount($site_id)) === false) {
            $this->flashMessage('messages.vote.internal_fail', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // check if user has voted for a server
        if($this->voteManager->hasVoted($site_id, $user_vote)) {
            // finish vote
            $this->voteManager->finishVote($this->user, $site_id, $in_count, $site->{VoteManager::SITE_COLUMN_POINTS});

            // log
            $this->logRepository->addLog($this->user, $this->current_ip, LogRepository::TYPE_VOTE_VOTED, $site_id);

            $this->flashMessage('messages.vote.success', 'success');
            $this->redirect('Vote:');
        } else {
            // current in count is not higher
            $this->flashMessage('messages.vote.failure', 'danger');
            $this->redrawControl('flashes');
        }
    }

    public function actionDefault()
    {
        $sites = $this->voteManager->findAllSites();
        $sites_voted = array();

        foreach ($sites_voted as $site) {
            $sites_voted[$site->{VoteManager::SITE_COLUMN_ID}] = false;
        }

        $this->template->sites = $sites;
    }
}