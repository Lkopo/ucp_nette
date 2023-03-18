<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 12:48
 */

namespace App\Model;

use App\Caching\PrivilegesCache;
use Nette;

class Authorizator implements Nette\Security\IAuthorizator
{
    /** @var Nette\Database\Context */
    private $database;

    /** @var PrivilegesCache */
    private $privilegesCache;

    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(Nette\Database\Context $database, PrivilegesCache $privilegesCache, AccountRepository $accountRepository)
    {
        $this->database = $database;
        $this->privilegesCache = $privilegesCache;
        $this->accountRepository = $accountRepository;
    }

    /**
     * Check if there is a privilege for resource in array of privileges
     *
     * @param array $privileges
     * @param $resource
     * @param $privilege
     * @return bool
     */
    private function hasPrivilege(array $privileges, $resource, $privilege)
    {
        foreach ($privileges as $p) {
            if($p[AccountRepository::PRIV_COLUMN_RESOURCE] == $resource && $p[AccountRepository::PRIV_COLUMN_PRIVILEGE] == $privilege)
                return true;
        }
        return false;
    }

    /**
     * RBAC = check if user with his roles has privilege for current resource
     * e.g. resource => MyAccount, privilege => default
     *
     * Cache user roles & load from cache
     *
     * @param $role
     * @param $resource
     * @param $privilege
     * @return bool
     */
    public function isAllowed($role, $resource, $privilege)
    {
        return $this->hasPrivilege($this->privilegesCache->get($role), $resource, $privilege);
    }

    /**
     * Get role IDs in array for user
     *
     * @param $user_id
     * @return array
     */
    public function getRolesForUser($user_id)
    {
        $rows = $this->accountRepository->findAllUserRolesById($user_id);
        $roles = array();

        $my_role = null;

        // fetch roles & store them in array
        foreach ($rows as $role) {
            $roles[] = $role->{AccountRepository::UR_COLUMN_ROLE};
            $my_role = $role->role; // store user's role with lowest level
        }

        // at least one special role is assigned
        if(count($roles) > 0) {
            // select lower roles than current
            $lower_roles = $this->accountRepository->findAllRolesLowerByLevel($my_role->level);

            foreach ($lower_roles as $lower_role) {
                if(!in_array($lower_role->id, $roles))
                    $roles[] = $lower_role->{AccountRepository::ACL_COLUMN_ID};
            }
        } else {
            // no roles; add default role Player
            $roles[] = AccountRepository::ROLE_PLAYER_ID;
        }

        return $roles;
    }
}