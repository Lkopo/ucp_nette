<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 14:51
 */

namespace App\Caching;

use App\Model\AccountRepository;

class RoleCache extends BaseCache
{
    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        parent::__construct('acl_roles');
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param $role_id
     * @return string
     */
    public function getName($role_id)
    {
        // try to load role from cache. If unsuccessful, load from DB & cache it.
        if(!($role_name = ($this->cache->load('role-' . $role_id)[AccountRepository::ACL_COLUMN_NAME]))) {
            $role = $this->accountRepository->findOneRoleById($role_id);
            if ($role) {
                $this->cache->save('role-' . $role_id, $role->toArray());
                return $role->{AccountRepository::ACL_COLUMN_NAME};
            }
            else
                return 'undefined';
        }
        return $role_name;
    }

    /**
     * @param $role_id
     * @return string
     */
    public function getLevel($role_id)
    {
        // try to load role from cache. If unsuccessful, load from DB & cache it.
        if(($role_level = ($this->cache->load('role-' . $role_id)[AccountRepository::ACL_COLUMN_LEVEL])) === NULL) {
            $role = $this->accountRepository->findOneRoleById($role_id);
            if ($role) {
                $this->cache->save('role-' . $role_id, $role->toArray());
                return $role->{AccountRepository::ACL_COLUMN_LEVEL};
            }
            else
                return 'undefined';
        }
        return $role_level;
    }
}