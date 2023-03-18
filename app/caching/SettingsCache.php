<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 16.03.2017
 * Time: 14:35
 */

namespace App\Caching;

use App\Model\SettingsManager;

class SettingsCache extends BaseCache
{
    /** @var SettingsManager */
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        parent::__construct('settings');
        $this->settingsManager = $settingsManager;
    }

    /**
     * @return array|\Nette\Database\Table\IRow|object
     */
    public function getAll()
    {
        if(!($settings = ($this->cache->load('page_settings')))) {
            $sett = $this->settingsManager->getSettings();
            if ($sett) {
                $this->cache->save('page_settings', $sett->toArray());
                return $sett;
            }
            else
                return array();
        }
        return (object) $settings;
    }

    /**
     * @param $key
     * @return string
     */
    public function get($key)
    {
        // try to load settings from cache. If unsuccessful, load from DB & cache it.
        if(($value = ($this->cache->load('page_settings')[$key])) === NULL) {
            $settings = $this->settingsManager->getSettings();
            if ($settings) {
                $this->cache->save('page_settings', $settings->toArray());
                return $settings->$key;
            }
            else
                return 'undefined';
        }
        return $value;
    }

    /**
     * @return void
     */
    public function purge()
    {
        $this->cache->remove('page_settings');
    }
}