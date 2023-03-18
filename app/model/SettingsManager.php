<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 17:01
 */

namespace App\Model;

use Nette;

class SettingsManager extends Nette\Object
{
    const
        RLMS_TABLE_NAME = 'realms',
        RLMS_COLUMN_ID = 'id',
        RLMS_COLUMN_NAME = 'name';

    const
        SETT_TABLE_NAME = 'web_settings',
        SETT_COLUMN_PAGE_NAME = 'page_name',
        SETT_COLUMN_PAGE_DESC = 'page_description',
        SETT_COLUMN_PAGE_KEYWORDS = 'page_keywords',
        SETT_COLUMN_PAGE_EMAIL = 'page_email',
        SETT_COLUMN_PAGE_EMAIL_SIGN = 'page_email_sign';

    const
        SERVICE_RENAME = 'rename',
        SERVICE_CUSTOMIZE = 'customize',
        SERVICE_CHANGERACE = 'changerace';

    const
        SRVC_TABLE_NAME = 'service_settings',
        SRVC_COLUMN_ID = 'id',
        SRVC_COLUMN_SERVICE = 'service',
        SRVC_COLUMN_PRICE = 'price',
        SRVC_COLUMN_REALM = 'realm_id';

    const
        MODULE_TYPE_STATUS = 'status';

    const
        MODULE_STATUS_DISABLED = 0,
        MODULE_STATUS_ENABLED = 1;

    const
        MODULE_RENAME = 'rename',
        MODULE_CUSTOMIZE = 'customize',
        MODULE_CHANGERACE = 'changerace',
        MODULE_CHARTRADE = 'chartrade',
        MODULE_VOTE = 'vote',
        MODULE_DONATE = 'donate';

    const
        MODULES_TABLE_NAME = 'module_settings',
        MODULES_COLUMN_ID = 'id',
        MODULES_COLUMN_NAME = 'module_name',
        MODULES_COLUMN_TYPE = 'type',
        MODULES_COLUMN_VALUE = 'value';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getRealms()
    {
        return $this->database->table(self::RLMS_TABLE_NAME);
    }

    /**
     * @param $realm_id
     * @return Nette\Database\Table\IRow
     */
    public function getRealmById($realm_id)
    {
        return $this->database->table(self::RLMS_TABLE_NAME)->get($realm_id);
    }

    /**
     * @param $name
     * @param $realm
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getServiceSettingByNameAndRealm($name, $realm)
    {
        return $this->database->table(self::SRVC_TABLE_NAME)
            ->where(self::SRVC_COLUMN_SERVICE, $name)
            ->where(self::SRVC_COLUMN_REALM, $realm)
            ->fetch();
    }

    /**
     * @param $name
     * @param $type
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getModuleSettingByNameAndType($name, $type)
    {
        return $this->database->table(self::MODULES_TABLE_NAME)
            ->where(self::MODULES_COLUMN_NAME, $name)
            ->where(self::MODULES_COLUMN_TYPE, $type)
            ->fetch();
    }

    /**
     * @param $name
     * @return Nette\Database\Table\Selection
     */
    public function findAllServiceSettingsByName($name)
    {
        return $this->database->table(self::SRVC_TABLE_NAME)
            ->where(self::SRVC_COLUMN_SERVICE, $name);
    }

    /**
     * @param $name
     * @return Nette\Database\Table\Selection
     */
    public function findAllModuleSettingsByName($name)
    {
        return $this->database->table(self::MODULES_TABLE_NAME)
            ->where(self::MODULES_COLUMN_NAME, $name);
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getStatusModules()
    {
        return $this->database->table(self::MODULES_TABLE_NAME)
            ->where(self::MODULES_COLUMN_TYPE, self::MODULE_TYPE_STATUS);
    }

    /**
     * @return Nette\Database\Table\IRow
     */
    public function getSettings()
    {
        return $this->database->table(self::SETT_TABLE_NAME)->get(1);
    }

    /**
     * @param $name
     * @param $realm
     * @param $price
     * @return bool
     */
    public function updateService($name, $realm, $price)
    {
        $service = $this->getServiceSettingByNameAndRealm($name, $realm);

        if($service) {
            $service->update([
                SettingsManager::SRVC_COLUMN_PRICE => $price
            ]);
            return true;
        }

        return false;
    }

    /**
     * @param $name
     * @param $type
     * @param $value
     * @return bool
     */
    public function updateModule($name, $type, $value)
    {
        $module = $this->getModuleSettingByNameAndType($name, $type);

        if($module) {
            $module->update([
                SettingsManager::MODULES_COLUMN_VALUE => $value
            ]);
            return true;
        }

        return false;
    }

    /**
     * @param $page_name
     * @param $page_desc
     * @param $page_keywords
     * @param $page_email
     * @param $page_email_sign
     * @return bool
     */
    public function changeSettings($page_name, $page_desc, $page_keywords, $page_email, $page_email_sign)
    {
        $settings = $this->database->table(self::SETT_TABLE_NAME)->get(1);
        $settings->update(array(
            self::SETT_COLUMN_PAGE_NAME => $page_name,
            self::SETT_COLUMN_PAGE_DESC => $page_desc,
            self::SETT_COLUMN_PAGE_KEYWORDS => $page_keywords,
            self::SETT_COLUMN_PAGE_EMAIL => $page_email,
            self::SETT_COLUMN_PAGE_EMAIL_SIGN => $page_email_sign
        ));

        return true;
    }
}