<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 14:04
 */

namespace App\Model;

use Nette;

class CharacterRepository extends Nette\Object
{
    const
        FLAG_RENAME = 1,
        FLAG_CUSTOMIZE = 8,
        FLAG_CHANGERACE = 128;

    const
        STATUS_ONLINE = 1,
        STATUS_OFFLINE = 0;

    const
        C_TABLE_NAME = 'characters',
        C_COLUMN_ID = 'guid',
        C_COLUMN_ACC_ID = 'account',
        C_COLUMN_REALM = 'realm_id',
        C_COLUMN_NAME = 'name',
        C_COLUMN_LEVEL = 'level',
        C_COLUMN_CLASS = 'class',
        C_COLUMN_MONEY = 'money',
        C_COLUMN_ONLINE = 'online',
        C_COLUMN_AT_LOGIN = 'at_login';

    const
        BAN_TABLE_NAME = 'character_banned',
        BAN_COLUMN_ID = 'guid',
        BAN_COLUMN_BANDATE = 'bandate',
        BAN_COLUMN_UNBANDATE = 'unbandate',
        BAN_COLUMN_BANNEDBY = 'bannedby',
        BAN_COLUMN_BANREASON = 'banreason',
        BAN_COLUMN_ACTIVE = 'active';

    const
        STATES_TABLE_NAME = 'worldstates',
        STATES_COLUMN_ENTRY = 'entry',
        STATES_COLUMN_VALUE = 'value',
        STATES_COLUMN_COMMENT = 'comment';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $account
     * @return \Nette\Database\Table\Selection
     */
    public function findByAccount($account)
    {
        return $this->database->table(self::C_TABLE_NAME)
            ->where(self::C_COLUMN_ACC_ID, $account)
            ->order(self::C_COLUMN_REALM)
            ->order(self::C_COLUMN_LEVEL . ' DESC')
            ->order(self::C_COLUMN_NAME);
    }

    /**
     * @param $account
     * @return array|null
     */
    public function findByAccountForSelection($account)
    {
        $rows = $this->database->table(self::C_TABLE_NAME)
            ->where(self::C_COLUMN_ACC_ID, $account)
            ->order(self::C_COLUMN_REALM)
            ->order(self::C_COLUMN_LEVEL . ' DESC')
            ->order(self::C_COLUMN_NAME)
            ->fetchPairs(self::C_COLUMN_ID, self::C_COLUMN_NAME);

        return ($rows ? $rows : NULL);
    }

    /**
     * @param $account
     * @param $realm
     * @return \Nette\Database\Table\Selection
     */
    public function findByAccountAndRealm($account, $realm)
    {
        return $this->database->table(self::C_TABLE_NAME)
            ->where(self::C_COLUMN_ACC_ID, $account)
            ->where(self::C_COLUMN_REALM, $realm)
            ->order(self::C_COLUMN_REALM)
            ->order(self::C_COLUMN_LEVEL . ' DESC')
            ->order(self::C_COLUMN_NAME);
    }

    /**
     * @param $guid
     * @return mixed|Nette\Database\Table\IRow
     */
    public function findOneById($guid)
    {
        return $this->database->table(self::C_TABLE_NAME)->get($guid);
    }

    /**
     * @param $name
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByName($name)
    {
        return $this->database->table(self::C_TABLE_NAME)->where(self::C_COLUMN_NAME, $name)->fetch();
    }

    /**
     * @param $name_id
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByNameOrId($name_id)
    {
        return $this->database->table(self::C_TABLE_NAME)
            ->whereOr([
                self::C_COLUMN_NAME => $name_id,
                self::C_COLUMN_ID => $name_id
            ])
            ->fetch();
    }

    /**
     * @return mixed
     */
    public function getNextArenaPointDistributionTime()
    {
        $row = $this->database->table(self::STATES_TABLE_NAME)
            ->where(self::STATES_COLUMN_ENTRY, 20001)
            ->fetch();

        return $row->value;
    }

    /**
     * @param $guid
     * @return bool
     */
    public function hasBan($guid)
    {
        $row = $this->database->table(self::BAN_TABLE_NAME)
            ->where(self::BAN_COLUMN_ID, $guid)
            ->where(self::BAN_COLUMN_UNBANDATE . ' >', time())
            ->where(self::BAN_COLUMN_ACTIVE, 1)
            ->fetch();

        if($row)
            return true;
        return false;
    }

    /**
     * @param Nette\Database\Table\IRow $character
     * @param $price
     * @return void
     */
    public function rename(Nette\Database\Table\IRow $character, $price)
    {
        $character->update([
            self::C_COLUMN_AT_LOGIN => self::FLAG_RENAME,
            self::C_COLUMN_MONEY => $character->{self::C_COLUMN_MONEY} - $price * 10000
        ]);
    }

    /**
     * @param Nette\Database\Table\IRow $character
     * @param $price
     * @return void
     */
    public function customize(Nette\Database\Table\IRow $character, $price)
    {
        $character->update([
            self::C_COLUMN_AT_LOGIN => self::FLAG_CUSTOMIZE,
            self::C_COLUMN_MONEY => $character->{self::C_COLUMN_MONEY} - $price * 10000
        ]);
    }

    /**
     * @param Nette\Database\Table\IRow $character
     * @param $price
     * @return void
     */
    public function changeRace(Nette\Database\Table\IRow $character, $price)
    {
        $character->update([
            self::C_COLUMN_AT_LOGIN => self::FLAG_CHANGERACE,
            self::C_COLUMN_MONEY => $character->{self::C_COLUMN_MONEY} - $price * 10000
        ]);
    }
}