<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 30.09.2017
 * Time: 12:57
 */

namespace App\Presenters;

use App\Model\SettingsManager;

class DonateStatsPresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
        $this->checkModuleEnabled(SettingsManager::MODULE_DONATE);
        $this->redirect('Homepage:'); // temporary disabled
    }

    public function renderDefault()
    {
        $this->template->top10donators = $this->donateManager->findTop10Donators();
        $this->template->distributionStats = $this->donateManager->getTotalDistributionStats();
        $this->template->paidStats = $this->donateManager->findPaidStats();
        $this->template->yearPaidStats = $this->donateManager->findLastMonthsStats(11);
    }
}