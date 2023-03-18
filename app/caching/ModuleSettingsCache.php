<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 18.08.2017
 * Time: 14:14
 */

namespace App\Caching;

use App\Model\SettingsManager;

class ModuleSettingsCache extends BaseCache
{
    /** @var SettingsManager */
    private $settingsManager;

    public function __construct(SettingsManager $settingsManager)
    {
        parent::__construct('module_settings');
        $this->settingsManager = $settingsManager;
    }

    /**
     * @param $name
     * @return mixed|string
     */
    public function getStatus($name)
    {
        // try to load module from cache. If unsuccessful, load from DB & cache it.
        if(($status = ($this->cache->load('module-' . $name)[SettingsManager::MODULE_TYPE_STATUS])) === NULL) {
            $modules = $this->settingsManager->findAllModuleSettingsByName($name);
            if($modules->count() > 0) {
                $modules_arr = [];

                foreach ($modules as $module) {
                    $modules_arr[$module->{SettingsManager::MODULES_COLUMN_TYPE}] = $module->{SettingsManager::MODULES_COLUMN_VALUE};
                }

                $this->cache->save('module-' . $name, $modules_arr);

                return $modules_arr[SettingsManager::MODULE_TYPE_STATUS];
            } else
                return 'undefined';
        }
        return $status;
    }

    /**
     * @param $name
     * @return void
     */
    public function purge($name)
    {
        $this->cache->remove('module-' . $name);
    }
}