<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 17:05
 */

namespace App\Latte;

use Nette;
use App\Caching\RealmNameCache;

/**
 * Change realm id into its name
 * Cache realm names
 *
 * Class RealmNameFilter
 * @package App\Latte
 */
class RealmNameFilter extends FilterHelper
{
    /** @var RealmNameCache */
    private $realmNameCache;

    public function __construct(Nette\Http\Request $httpRequest, RealmNameCache $realmNameCache)
    {
        parent::__construct($httpRequest);
        $this->realmNameCache = $realmNameCache;
    }

    /**
     * @param $realm_id
     * @return mixed|string
     */
    public function __invoke($realm_id)
    {
        return $this->realmNameCache->get($realm_id);
    }
}