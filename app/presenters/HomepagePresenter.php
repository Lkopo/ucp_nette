<?php

namespace App\Presenters;

use App\Model\LogRepository;

class HomepagePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function renderDefault()
    {
        $this->template->login_logs = $this->logRepository->findLimitedByAcountAndType($this->user, LogRepository::TYPE_AUTH_LOGIN, 10);
        $this->template->login_fails = $this->logRepository->findLimitedByAcountAndType($this->user, LogRepository::TYPE_AUTH_LOGIN_FAILED, 10);
    }
}
