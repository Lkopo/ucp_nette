<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 22.04.2017
 * Time: 15:51
 */

namespace App\Latte;

use Nette;
use App\Caching\RoleCache;

/**
 * Get role level from cache or DB
 *
 * Class RoleLevelFilter
 * @package App\Latte
 */
class RoleLevelFilter extends FilterHelper
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
        return $this->roleCache->getLevel($role_id);
    }
}