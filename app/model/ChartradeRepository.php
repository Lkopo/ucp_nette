<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 04.03.2017
 * Time: 17:37
 */

namespace App\Model;

use App\Utils\TrinityCore;
use Nette;

class ChartradeRepository extends Nette\Object
{
    const
        STATUS_ACTIVE = 0,
        STATUS_ACCEPTED = 1,
        STATUS_CANCELLED = 2;

    const
        VERIFY_PREMADE = 1,
        VERIFY_OFFERER = 2,
        VERIFY_REQUESTED = 0;

    const
        TABLE_NAME = 'chartrade',
        COLUMN_ID = 'id',
        COLUMN_CREATED_TIME = 'created_time',
        COLUMN_OFFERER = 'offerer_guid',
        COLUMN_OFFERER_ACC = 'offerer_account',
        COLUMN_OFFERER_NAME = 'offerer_name',
        COLUMN_OFFERER_CLASS = 'offerer_class',
        COLUMN_OFFERER_RACE = 'offerer_race',
        COLUMN_OFFERER_GENDER = 'offerer_gender',
        COLUMN_OFFERER_LEVEL = 'offerer_level',
        COLUMN_OFFERER_MONEY = 'offerer_money',
        COLUMN_OFFERER_REALM = 'offerer_realm',
        COLUMN_OFFERER_IP = 'offerer_ip',
        COLUMN_REQUESTED = 'requested_guid',
        COLUMN_REQUESTED_ACC = 'requested_account',
        COLUMN_REQUESTED_NAME = 'requested_name',
        COLUMN_REQUESTED_CLASS = 'requested_class',
        COLUMN_REQUESTED_RACE = 'requested_race',
        COLUMN_REQUESTED_GENDER = 'requested_gender',
        COLUMN_REQUESTED_LEVEL = 'requested_level',
        COLUMN_REQUESTED_MONEY = 'requested_money',
        COLUMN_REQUESTED_REALM = 'requested_realm',
        COLUMN_REQUESTED_IP = 'requested_ip',
        COLUMN_VERIFY_TYPE = 'verify_type',
        COLUMN_CLOSED_TIME = 'closed_time',
        COLUMN_HASH = 'hash',
        COLUMN_STATUS = 'status';

    /** @var Nette\Database\Context */
    private $database;

    /** @var CharacterRepository */
    private $characterRepository;

    public function __construct(Nette\Database\Context $database, CharacterRepository $characterRepository)
    {
        $this->database = $database;
        $this->characterRepository = $characterRepository;
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneById($id)
    {
        return $this->database->table(self::TABLE_NAME)->get($id);
    }

    /**
     * @param array $params
     * @return \Nette\Database\Table\Selection
     */
    public function findAllWithParams(array $params)
    {
        $select = $this->database->table(self::TABLE_NAME);

        foreach ($params as $param => $value) {
            switch ($param) {
                case 'acc':
                    $select->whereOr([
                        self::COLUMN_OFFERER_ACC => $value,
                        self::COLUMN_REQUESTED_ACC => $value
                    ]);
                    break;
                case 'char':
                    $select->whereOr([
                        self::COLUMN_OFFERER => $value,
                        self::COLUMN_OFFERER_NAME => $value,
                        self::COLUMN_REQUESTED => $value,
                        self::COLUMN_REQUESTED_NAME => $value
                    ]);
                    break;
                case 'ip':
                    $select->whereOr([
                        self::COLUMN_OFFERER_IP . ' LIKE ?' => $value,
                        self::COLUMN_REQUESTED_IP . ' LIKE ?' => $value
                    ]);
                    break;
                case 'cancelled':
                    if((int) $value == 1) {
                        $select->where(self::COLUMN_STATUS, [self::STATUS_ACCEPTED, self::STATUS_CANCELLED]);
                    } else {
                        $select->where(self::COLUMN_STATUS, self::STATUS_ACCEPTED);
                    }
            }
        }

        return $select->order(self::COLUMN_CLOSED_TIME . ' DESC');
    }

    /**
     * @param $account
     * @return \Nette\Database\Table\Selection
     */
    public function findVerifiedByAccount($account)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_VERIFY_TYPE . ' != ', self::VERIFY_PREMADE)
            ->whereOr([
                self::COLUMN_OFFERER_ACC => $account,
                self::COLUMN_REQUESTED_ACC => $account
            ])
            ->where(self::COLUMN_STATUS, self::STATUS_ACTIVE);
    }

    /**
     * @param $key
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByKey($key)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_HASH, $key)
            ->where(self::COLUMN_STATUS, self::STATUS_ACTIVE)
            ->fetch();
    }

    /**
     * @param $account
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByOffered($account)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_OFFERER_ACC, $account)
            ->where(self::COLUMN_STATUS, self::STATUS_ACTIVE)
            ->fetch();
    }

    /**
     * @param $account
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneByRequested($account)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_REQUESTED_ACC, $account)
            ->where(self::COLUMN_STATUS, self::STATUS_ACTIVE)
            ->fetch();
    }

    /**
     * Premake trade offer, needs validation
     *
     * @param Nette\Database\Table\ActiveRow $offerer
     * @param $offerer_ip
     * @param Nette\Database\Table\ActiveRow $requested
     * @param $requested_ip
     * @return string
     */
    public function preMakeOffer(Nette\Database\Table\ActiveRow $offerer, $offerer_ip, Nette\Database\Table\ActiveRow $requested, $requested_ip)
    {
        $key = TrinityCore::createKey(array(
            $offerer->guid, $offerer->account, $offerer_ip, $requested->guid, $requested->account, $requested_ip
        ));

        $this->database->table(self::TABLE_NAME)->insert(array(
            self::COLUMN_CREATED_TIME => time(),
            self::COLUMN_OFFERER => $offerer->guid,
            self::COLUMN_OFFERER_ACC => $offerer->account,
            self::COLUMN_OFFERER_NAME => $offerer->name,
            self::COLUMN_OFFERER_CLASS => $offerer->class,
            self::COLUMN_OFFERER_RACE => $offerer->race,
            self::COLUMN_OFFERER_GENDER => $offerer->gender,
            self::COLUMN_OFFERER_LEVEL => $offerer->level,
            self::COLUMN_OFFERER_MONEY => $offerer->money,
            self::COLUMN_OFFERER_REALM => $offerer->realm_id,
            self::COLUMN_OFFERER_IP => $offerer_ip,
            self::COLUMN_REQUESTED => $requested->guid,
            self::COLUMN_REQUESTED_ACC => $requested->account,
            self::COLUMN_REQUESTED_NAME => $requested->name,
            self::COLUMN_REQUESTED_CLASS => $requested->class,
            self::COLUMN_REQUESTED_RACE => $requested->race,
            self::COLUMN_REQUESTED_GENDER => $requested->gender,
            self::COLUMN_REQUESTED_LEVEL => $requested->level,
            self::COLUMN_REQUESTED_MONEY => $requested->money,
            self::COLUMN_REQUESTED_REALM => $requested->realm_id,
            self::COLUMN_REQUESTED_IP => $requested_ip,
            self::COLUMN_VERIFY_TYPE => self::VERIFY_PREMADE,
            self::COLUMN_STATUS => self::STATUS_ACTIVE,
            self::COLUMN_HASH => $key
        ));

        return $key;
    }

    /**
     * Verify pre made offer by offerer
     *
     * @param Nette\Database\Table\ActiveRow $trade
     * @return string
     */
    public function verifyPreMade(Nette\Database\Table\ActiveRow $trade)
    {
        $key = TrinityCore::createKey(array(
            $trade->{self::COLUMN_ID}, $trade->{self::COLUMN_OFFERER}, $trade->{self::COLUMN_REQUESTED}
        ));

        $trade->update(array(
            self::COLUMN_VERIFY_TYPE => self::VERIFY_OFFERER,
            self::COLUMN_HASH => $key
        ));

        return $key;
    }

    /**
     * Verify made offer by requested player
     *
     * @param Nette\Database\Table\ActiveRow $trade
     */
    public function verifyOffer(Nette\Database\Table\ActiveRow $trade)
    {
        $trade->update(array(
            self::COLUMN_VERIFY_TYPE => self::VERIFY_REQUESTED,
            self::COLUMN_HASH => NULL
        ));
    }

    /**
     * Cancel trade offer
     *
     * @param Nette\Database\Table\ActiveRow $trade
     */
    public function cancelTrade(Nette\Database\Table\ActiveRow $trade)
    {
        $offerer = $this->characterRepository->findOneById($trade->{self::COLUMN_OFFERER});
        $requested = $this->characterRepository->findOneById($trade->{self::COLUMN_REQUESTED});

        $trade->update(array(
            self::COLUMN_OFFERER_NAME => $offerer->name,
            self::COLUMN_OFFERER_RACE => $offerer->race,
            self::COLUMN_OFFERER_GENDER => $offerer->gender,
            self::COLUMN_OFFERER_LEVEL => $offerer->level,
            self::COLUMN_OFFERER_MONEY => $offerer->money,
            self::COLUMN_REQUESTED_NAME => $requested->name,
            self::COLUMN_REQUESTED_GENDER => $requested->gender,
            self::COLUMN_REQUESTED_LEVEL => $requested->level,
            self::COLUMN_REQUESTED_MONEY => $requested->money,
            self::COLUMN_CLOSED_TIME => time(),
            self::COLUMN_STATUS => self::STATUS_CANCELLED
        ));
    }

    /**
     * Make a trade - trade two characters between 2 accounts
     *
     * @param Nette\Database\Table\ActiveRow $trade
     */
    public function makeTrade(Nette\Database\Table\ActiveRow $trade)
    {
        $offerer = $this->characterRepository->findOneById($trade->{self::COLUMN_OFFERER});
        $requested = $this->characterRepository->findOneById($trade->{self::COLUMN_REQUESTED});

        // prepare names & account ids for switch
        $new_offerer_name = $requested->name;
        $new_offerer_account = $requested->account;
        $new_requested_name = $offerer->name;
        $new_requested_account = $offerer->account;

        $offerer->update(array(
            CharacterRepository::C_COLUMN_NAME => $new_offerer_name,
            CharacterRepository::C_COLUMN_ACC_ID => $new_offerer_account
        ));

        $requested->update(array(
            CharacterRepository::C_COLUMN_NAME => $new_requested_name,
            CharacterRepository::C_COLUMN_ACC_ID => $new_requested_account
        ));

        // update status to accepted
        $trade->update(array(
            self::COLUMN_OFFERER_NAME => $new_requested_name,
            self::COLUMN_OFFERER_RACE => $offerer->race,
            self::COLUMN_OFFERER_GENDER => $offerer->gender,
            self::COLUMN_OFFERER_LEVEL => $offerer->level,
            self::COLUMN_OFFERER_MONEY => $offerer->money,
            self::COLUMN_REQUESTED_NAME => $new_offerer_name,
            self::COLUMN_REQUESTED_RACE => $requested->race,
            self::COLUMN_REQUESTED_GENDER => $requested->gender,
            self::COLUMN_REQUESTED_LEVEL => $requested->level,
            self::COLUMN_REQUESTED_MONEY => $requested->money,
            self::COLUMN_CLOSED_TIME => time(),
            self::COLUMN_STATUS => self::STATUS_ACCEPTED
        ));
    }
}