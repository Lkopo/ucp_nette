<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 14:01
 */

namespace App\Forms;

use App\Caching\RoleCache;
use App\Model\AccountRepository;
use App\Model\UserManager;
use Nette\Application\UI\Form;
use Nette\Security\User;

class RoledUserFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;

    /** @var RoleCache */
    private $roleCache;

    /** @var UserManager */
    private $userManager;

    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(User $user, RoleCache $roleCache, UserManager $userManager, AccountRepository $accountRepository)
    {
        $this->user = $user;
        $this->roleCache = $roleCache;
        $this->userManager = $userManager;
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param $username_id
     * @return bool
     */
    public function usernameOrIdExists($username_id)
    {
        $user = $this->userManager->findOneByUsernameOrId($username_id);

        if(!$user)
            return false;

        return true;
    }

    /**
     * @param $username_id
     * @return bool
     */
    public function alreadyHasRole($username_id)
    {
        $user = $this->userManager->findOneByUsernameOrId($username_id);

        if(!$user)
            return false;

        $roles = $this->accountRepository->findAllUserRolesById($user);
        return $roles->count() > 0;
    }

    /**
     * @param $level
     * @return bool
     */
    public function isHigherLevelThanPlayer($level)
    {
        return (int) $level > AccountRepository::ROLE_PLAYER;
    }

    /**
     * @return Form
     */
    public function create($editing = FALSE)
    {
        $form = new Form;
        $form->setTranslator($this->translator);

        $t = $this->translator;

        $my_role_lvl = $this->roleCache->getLevel($this->user->roles[0]);
        $roles = $this->accountRepository->findAllRolesLowerByLevelForSelection($my_role_lvl);

        $form->addGroup();
        $form->addText('username_id', 'forms.manage_users.username_id')
            ->setRequired('forms.manage_users.username_id_empty')
            ->addRule(function ($control) {
                return $this->usernameOrIdExists($control->value);
            }, 'forms.manage_users.username_id_notfound')
            ->addRule(function ($control) {
                return !$this->alreadyHasRole($control->value);
            }, 'forms.manage_users.user_has_role');

        if($editing)
            $form['username_id']->setDisabled(TRUE);

        $form->addSelect('role', $t->trans('forms.manage_users.select_role'), $roles)
            ->setPrompt($t->trans('forms.manage_users.choose_role'))
            ->setTranslator(NULL)
            ->setRequired('forms.manage_users.select_empty')
            ->addRule(function ($control) {
                return $this->isHigherLevelThanPlayer($control->value);
            }, $t->trans('forms.manage_users.select_low_value'));

        $form->addGroup('');

        $form->addSubmit('submit', ($editing ? 'forms.manage_users.confirm_submit' : 'forms.manage_users.add_submit'))
            ->setAttribute('class', 'btn-success');

        $form->addProtection('forms.global.csrf_expired');

        return $form;
    }
}