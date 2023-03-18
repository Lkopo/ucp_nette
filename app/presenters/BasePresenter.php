<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.02.2017
 * Time: 15:49
 */

namespace App\Presenters;

use App\Caching\ModuleSettingsCache;
use App\Caching\RoleCache;
use App\Caching\ServiceSettingsCache;
use App\Caching\SettingsCache;
use App\Model\AccountRepository;
use App\Model\CharacterRepository;
use App\Model\ChartradeRepository;
use App\Model\DonateManager;
use App\Model\LogRepository;
use App\Model\SettingsManager;
use App\Model\UserManager;
use App\Model\VoteManager;
use Nette;
use Tracy\Debugger;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    use \Nextras\Application\UI\SecuredLinksPresenterTrait;

    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    /** @var UserManager @inject */
    public $userManager;

    /** @var ChartradeRepository @inject */
    public $chartradeRepository;

    /** @var AccountRepository @inject */
    public $accountRepository;

    /** @var SettingsManager @inject */
    public $settingsManager;

    /** @var CharacterRepository @inject */
    public $characterRepository;

    /** @var LogRepository @inject */
    public $logRepository;

    /** @var VoteManager @inject */
    public $voteManager;

    /** @var DonateManager @inject */
    public $donateManager;

    /** @var SettingsCache @inject */
    public $settingsCache;

    /** @var ServiceSettingsCache @inject */
    public $serviceSettingsCache;

    /** @var ModuleSettingsCache @inject */
    public $moduleSettingsCache;

    /** @var RoleCache @inject */
    public $roleCache;

    public $current_ip;

    protected function startup()
    {
        parent::startup();

        $this->current_ip = $this->getHttpRequest()->getRemoteAddress();
    }

    public function beforeRender()
    {
        $this->template->settings = $this->settingsCache->getAll();
        $this->template->serviceSettings = $this->serviceSettingsCache;
        $this->template->moduleSettings = $this->moduleSettingsCache;
        $this->template->settingsManager = $this->settingsManager;
        $this->template->characterRepository = $this->characterRepository;
        $this->template->chartradeRepository = $this->chartradeRepository;
        $this->template->logRepository = $this->logRepository;
        $this->template->voteManager = $this->voteManager;
        $this->template->donateManager = $this->donateManager;
        $this->template->locale = $this->locale;
        $this->template->current_ip = $this->current_ip;
        $this->template->userManager = $this->userManager;
        $this->template->accountRepository = $this->accountRepository;

        if($this->user->isLoggedIn()) {
            if($this->user->id != AccountRepository::SAFEMODE_ADMIN_ID) {
                $this->template->isUserBanned = $this->userManager->hasBan($this->user->id);
                $this->template->countOffers = $this->chartradeRepository->findVerifiedByAccount($this->user->id)->count();
                $this->template->role = $this->accountRepository->findOneRoleById($this->user->roles[0]);
                $this->template->userVotePoints = (!($user_points = $this->userManager->findOneUserVotePointsByUserId($this->user->id)) ? 0 : $user_points->{UserManager::VOTEPOINTS_COLUMN_POINTS});
                $this->template->userDonatePoints = (!($user_points = $this->userManager->findOneUserDonatePointsByUserId($this->user->id)) ? 0 : $user_points->{UserManager::DONATEPOINTS_COLUMN_POINTS});
            } else {
                $this->template->isUserBanned = false;
                $this->template->countOffers = 0;
                $this->template->role = $this->accountRepository->findOneRoleById($this->user->roles[0]);
                $this->template->userVotePoints = 0;
            }
        }
    }

    public function generateUrlLink($resource, array $params)
    {
        // set persistent parameters to NULL
        $params['backlink'] = NULL;

        return $this->link('//' . $resource, $params);
    }

    public function checkUserLoggedIn()
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage('messages.user.login_to_continue', 'warning');
            $this->redirect('Auth:login', array('backlink' => $this->storeRequest()));
        }
    }

    public function checkUserPermissions()
    {
        // first check, if role did not change
        $this->checkUserRoleChanged();

        // safemode check
        if($this->user->id == AccountRepository::SAFEMODE_ADMIN_ID) {
            if (!(array_key_exists($this->name, AccountRepository::SAFEMODE_ENABLED_MODULES)
                && in_array($this->action, AccountRepository::SAFEMODE_ENABLED_MODULES[$this->name]))) {
                $this->flashMessage('messages.user.access_denied', 'danger');
                $this->redirect('Homepage:');
            } else {
                return; // safe mode user authorized do not continue
            }
        }

        if(!$this->user->isAllowed($this->name, $this->action)) {
            $this->flashMessage('messages.user.access_denied', 'danger');
            $this->redirect('Homepage:');
        }
    }

    public function checkUserRoleChanged()
    {
        // safemode check
        if($this->user->id == AccountRepository::SAFEMODE_ADMIN_ID)
            return false;

        $role = $this->accountRepository->findAllUserRolesById($this->user->id)->fetch();

        if((!$role && $this->user->roles[0] != AccountRepository::ROLE_PLAYER) ||
            ($role && $role->{AccountRepository::UR_COLUMN_ROLE} != $this->user->roles[0]))
        {
            $this->flashMessage('messages.user.role_changed', 'warning');
            $this->getUser()->logout(); // logout user

            $this->redirect('Auth:');
        }
    }

    public function checkUserBanned()
    {
        if($this->userManager->hasBan($this->user->id))
            $this->redirect('Homepage:');
    }

    public function checkModuleEnabled($name)
    {
        $module = $this->moduleSettingsCache->getStatus($name);

        if($module !== 'undefined' && $module == SettingsManager::MODULE_STATUS_DISABLED) {
            $this->flashMessage('messages.module_settings.module_disabled', 'danger');

            $this->redirect('Homepage:');
        }
    }
}