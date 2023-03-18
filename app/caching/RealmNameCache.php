<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 15:01
 */

namespace App\Caching;

use App\Model\SettingsManager;

class RealmNameCache extends BaseCache
{
    /** @var SettingsManager */
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        parent::__construct('realm_names');
        $this->settingsManager = $settingsManager;
    }

    /**
     * @param $realm_id
     * @return mixed|string
     */
    public function get($realm_id)
    {
        // try to load realm name from cache. If unsuccessful, load from DB & cache it.
        if(($realm_name = $this->cache->load('realm_name-' . $realm_id)) === NULL) {
            $realm = $this->settingsManager->getRealmById($realm_id);
            if ($realm) {
                $this->cache->save('realm_name-' . $realm_id, $realm->name);
                return $realm->name;
            }
            else
                return 'undefined';
        }
        return $realm_name;
    }
}