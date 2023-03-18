<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 15:05
 */

namespace App\Caching;

use App\Model\AccountRepository;

class PrivilegesCache extends BaseCache
{
    /** @var AccountRepository */
    private $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        parent::__construct('acl_role_privileges');
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param $role_id
     * @return array|mixed
     */
    public function get($role_id)
    {
        // check if privilege list is already cached
        if(($cached_privileges = $this->cache->load('role-' . $role_id)) === NULL) {
            // cache privileges
            $role_privileges = $this->accountRepository->findAllPrivilegesByRole($role_id);
            $priv_arr = array();
            if($role_privileges) {
                foreach ($role_privileges as $role_privilege)
                    $priv_arr[] = $role_privilege->toArray(); // convert ActiveRow instance into array
            }

            // store a privilege list into cache
            $this->cache->save('role-' . $role_id, $priv_arr);

            return $priv_arr;
        }

        // successfuly loaded from cache
        return $cached_privileges;
    }
}