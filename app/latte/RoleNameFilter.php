<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 16:06
 */

namespace App\Latte;

use App\Caching\RoleCache;
use Nette;

/**
 * Get role name from cache or DB
 *
 * Class RoleNameFilter
 * @package App\Latte
 */
class RoleNameFilter extends FilterHelper
{
    /** @var RoleCache */
    private $roleCache;

    public function __construct(Nette\Http\Request $httpRequest, RoleCache $roleCache)
    {
        parent::__construct($httpRequest);
        $this->roleCache = $roleCache;
    }

    /**
     * @param $role_id
     * @return string
     */
    public function __invoke($role_id)
    {
        return $this->roleCache->getName($role_id);
    }
}