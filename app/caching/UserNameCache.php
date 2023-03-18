<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 11:56
 */

namespace App\Caching;

use App\Model\UserManager;

class UserNameCache extends BaseCache
{
    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        parent::__construct('usernames');
        $this->userManager = $userManager;
    }

    /**
     * @param $user_id
     * @return mixed|string
     */
    public function get($user_id)
    {
        // try to load username from cache. If unsuccessful, load from DB & cache it.
        if(($username = $this->cache->load('username-' . $user_id)) === NULL) {
            $user = $this->userManager->findOneById($user_id);
            if ($user) {
                $this->cache->save('username-' . $user_id, $user->username);
                return $user->username;
            }
            else
                return 'undefined';
        }
        return $username;
    }
}