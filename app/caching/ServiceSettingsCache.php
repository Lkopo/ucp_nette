<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 27.03.2017
 * Time: 12:25
 */

namespace App\Caching;

use App\Model\SettingsManager;

class ServiceSettingsCache extends BaseCache
{
    /** @var SettingsManager */
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        parent::__construct('service_settings');
        $this->settingsManager = $settingsManager;
    }

    /**
     * @param $name
     * @param $realm
     * @return mixed|string
     */
    public function getPrice($name, $realm)
    {
        // try to load setting from cache. If unsuccessful, load from DB & cache it.
        if(($price = ($this->cache->load('service-' . $name)[$realm])) === NULL) {
            $services = $this->settingsManager->findAllServiceSettingsByName($name);
            if ($services->count() > 0) {
                $services_arr = array();

                foreach($services as $service) {
                    $services_arr[$service->{SettingsManager::SRVC_COLUMN_REALM}] = $service->{SettingsManager::SRVC_COLUMN_PRICE};
                }

                $this->cache->save('service-' . $name, $services_arr);

                return $services_arr[$realm];
            }
            else
                return 'undefined';
        }
        return $price;
    }

    /**
     * @param $name
     * @return void
     */
    public function purge($name)
    {
        $this->cache->remove('service-' . $name);
    }
}