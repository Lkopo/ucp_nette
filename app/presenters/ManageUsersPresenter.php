<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 12:34
 */

namespace App\Presenters;

use App\Caching\RoleCache;
use App\Forms\RoledUserFormFactory;
use App\Forms\BaseFormFactory;
use App\Model\AccountRepository;
use App\Model\UserManager;
use Nette\Application\UI\Form;

class ManageUsersPresenter extends BasePresenter
{
    /** @var RoledUserFormFactory @inject */
    public $addRoledUserFormFactory;

    /** @var RoleCache @inject */
    public $roleCache;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->roleCache = $this->roleCache;
    }

    protected function startup()
    {
        parent::startup();
        $this->checkUserLoggedIn();
        $this->checkUserPermissions();
    }

    public function createComponentRoledUserForm()
    {
        $id = $this->getParameter('id');
        if($id)
            $form = $this->addRoledUserFormFactory->create(TRUE);
        else
            $form = $this->addRoledUserFormFactory->create();

        $form->onSuccess[] = [$this, 'roledUserFormSucceeded'];
        $form = BaseFormFactory::addBootstrap3Styles($form);

        return $form;
    }

    public function roledUserFormSucceeded(Form $form, $values)
    {
        $id = $this->getParameter('id');
        if($id) {
            if($this->accountRepository->changeRoleOfUser($id, $values->role)) {
                $this->flashMessage('messages.manage_users.successfuly_changed', 'success');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        } else {
            $user = $this->userManager->findOneByUsernameOrId($values->username_id);

            if(!$user) {
                $this->flashMessage('forms.manage_users.username_id_notfound', 'danger');
            }

            if ($this->accountRepository->addRoleToUser($user, $values->role)) {
                $this->flashMessage('messages.manage_users.successfuly_added', 'success');
                $this->redirect('default');
            } else {
                $this->flashMessage('messages.global.something_wrong', 'danger');
            }
        }
    }

    /** @secured */
    public function handleDelete($user_id)
    {
        if(!$this->isAjax())
            return;

        if($user_id == $this->user->id) {
            $this->flashMessage('messages.manage_users.delete_yourself', 'danger');
            $this->redrawControl('flashes');
            return;
        }

        // find user with that role
        $roles = $this->accountRepository->findAllUserRolesById($user_id);

        // check if user is going to delete higher or equal role
        $role = $this->accountRepository->findOneRoleById($roles->fetch()->{AccountRepository::PRIV_COLUMN_ROLE});
        $my_role = $this->accountRepository->findOneRoleById($this->user->roles[0]);
        if($role->{AccountRepository::ACL_COLUMN_LEVEL} >= $my_role->{AccountRepository::ACL_COLUMN_LEVEL}) {
            $this->flashMessage('messages.manage_users.delete_higher_role', 'danger');
            $this->redrawControl('flashes');
            return;
        }
        $roles->delete();

        $this->flashMessage('messages.manage_users.successfuly_deleted', 'success');
        $this->redrawControl('flashes');
        $this->redrawControl('roledUsersList');
    }

    public function renderEdit($id)
    {
        if($id == $this->user->id) {
            $this->flashMessage('messages.manage_users.edit_yourself', 'danger');
            $this->redirect('default');
        }

        $roles = $this->accountRepository->findAllUserRolesById($id);

        if($roles->count() <= 0) {
            $this->flashMessage('messages.manage_users.roled_user_notfound');
            $this->redirect('default');
        }

        $user = $this->userManager->findOneById($id);

        if(!$user) {
            $this->flashMessage('messages.manage_users.user_notfound');
            $this->redirect('default');
        }

        // check if user is going to delete higher or equal role
        $role = $this->accountRepository->findOneRoleById($roles->fetch()->{AccountRepository::PRIV_COLUMN_ROLE});
        $my_role = $this->accountRepository->findOneRoleById($this->user->roles[0]);
        if($role->{AccountRepository::ACL_COLUMN_LEVEL} >= $my_role->{AccountRepository::ACL_COLUMN_LEVEL}) {
            $this->flashMessage('messages.manage_users.edit_higher_role', 'danger');
            $this->redirect('default');
        }

        /*
         * Bug in Nette which was fixed and then revered, not fixed anymore
         * https://github.com/nette/nette/issues/961
         *
         * changed setDefaults to setValues for this form
         * */
        $this['roledUserForm']->setValues([
            'username_id' => $user->{UserManager::ACC_COLUMN_NAME},
            'role' => $role->{AccountRepository::ACL_COLUMN_ID}
        ]);
    }

    public function renderDefault()
    {
        $this->template->roled_users = $this->accountRepository->findAllPrivilegedUsers();
    }
}