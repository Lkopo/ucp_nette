<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 19.03.2017
 * Time: 12:00
 */

namespace App\Latte;

use App\Caching\UserNameCache;
use Nette;

/**
 * Load username from cache based on user's ID
 *
 * Class UserNameFilter
 * @package App\Latte
 */
class UserNameFilter extends FilterHelper
{
    /** @var UserNameCache */
    private $userNameCache;

    public function __construct(Nette\Http\Request $httpRequest, UserNameCache $userNameCache)
    {
        parent::__construct($httpRequest);
        $this->userNameCache = $userNameCache;
    }

    /**
     * @param $user_id
     * @return mixed|string
     */
    public function __invoke($user_id)
    {
        return $this->userNameCache->get($user_id);
    }
}