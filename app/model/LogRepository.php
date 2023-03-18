<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 11.03.2017
 * Time: 13:25
 */

namespace App\Model;

use Nette;

class LogRepository extends Nette\Object
{
    const
        /* AUTH */
        TYPE_AUTH_LOGIN = 1,
        TYPE_AUTH_LOGOUT = 2,
        TYPE_AUTH_LOGIN_FAILED = 3,
        TYPE_AUTH_REGISTER = 4,
        TYPE_AUTH_LOSTPASS = 5,
        TYPE_AUTH_LOSTPASS_CONFIRMED = 6,
        TYPE_AUTH_CHANGEPASS = 7,
        TYPE_AUTH_CHANGEPASS_CONFIRMED = 8,
        TYPE_AUTH_ACC_LOCK = 9,
        TYPE_AUTH_ACC_UNLOCK = 10,
        /* MY CHARACTERS */
        TYPE_CHAR_RENAME = 11,
        TYPE_CHAR_CUSTOMIZE = 12,
        TYPE_CHAR_CHANGERACE = 13,
        TYPE_CHAR_CHANGEFACTION = 14, // migration
        /* CHARTRADE */
        TYPE_TRADE_OFFER_CREATED = 15,
        TYPE_TRADE_OFFER_VERIFIED_OFFERER = 16,
        TYPE_TRADE_OFFER_VERIFIED_REQUESTED = 17,
        TYPE_TRADE_OFFER_ACCEPTED = 18,
        TYPE_TRADE_OFFER_CANCELLED = 19,
        /* VOTING */
        TYPE_VOTE_VOTED = 20,
        /* DONATE */
        TYPE_DONATE_DONATED = 21;

    /**
     * List of active user log types to display
     */
    const
        USER_TYPES_LIST = [
            self::TYPE_AUTH_LOGIN,
            self::TYPE_AUTH_LOGOUT,
            self::TYPE_AUTH_LOGIN_FAILED,
            self::TYPE_AUTH_REGISTER,
            self::TYPE_AUTH_LOSTPASS,
            self::TYPE_AUTH_LOSTPASS_CONFIRMED,
            self::TYPE_AUTH_CHANGEPASS,
            self::TYPE_AUTH_CHANGEPASS_CONFIRMED,
            self::TYPE_AUTH_ACC_LOCK,
            self::TYPE_AUTH_ACC_UNLOCK,
            self::TYPE_CHAR_RENAME,
            self::TYPE_CHAR_CUSTOMIZE,
            self::TYPE_CHAR_CHANGERACE,
            /*self::TYPE_CHAR_CHANGEFACTION,
            self::TYPE_TRADE_OFFER_CREATED,
            self::TYPE_TRADE_OFFER_VERIFIED_OFFERER,
            self::TYPE_TRADE_OFFER_VERIFIED_REQUESTED,
            self::TYPE_TRADE_OFFER_ACCEPTED,
            self::TYPE_TRADE_OFFER_CANCELLED*/
            self::TYPE_VOTE_VOTED,
            //self::TYPE_DONATE_DONATED,
        ];

    const
        TABLE_NAME = 'logs',
        COLUMN_ID = 'id',
        COLUMN_ACCOUNT = 'account_id',
        COLUMN_IP = 'ip',
        COLUMN_TYPE = 'type',
        COLUMN_ACTION = 'action',
        COLUMN_COMMENT = 'comment',
        COLUMN_TIME = 'time';

    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param $account
     * @param $type
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByAccountAndType($account, $type)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ACCOUNT, $account->id)
            ->where(self::COLUMN_TYPE, $type)
            ->order(self::COLUMN_TIME . ' DESC');
    }

    /**
     * @param array $params
     * @return \Nette\Database\Table\Selection
     */
    public function findAllByParams(array $params)
    {
        $select = $this->database->table(self::TABLE_NAME);

        foreach ($params as $key => $value) {
            if($key == self::COLUMN_IP)
                $select->where($key . ' LIKE ?', $value); // allow using wildcards for IPs
            else
                $select->where($key, $value);
        }

        // ignore safemode logs
        $select->where(self::COLUMN_ACCOUNT . ' != ?', AccountRepository::SAFEMODE_ADMIN_ID);

        return $select->order(self::COLUMN_TIME . ' DESC');
    }

    /**
     * @param $account
     * @param $type
     * @param $limit
     * @return \Nette\Database\Table\Selection
     */
    public function findLimitedByAcountAndType($account, $type, $limit)
    {
        return $this->database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ACCOUNT, $account->id)
            ->where(self::COLUMN_TYPE, $type)
            ->order(self::COLUMN_TIME . ' DESC')
            ->limit($limit);
    }

    /**
     * @param $limit
     * @return \Nette\Database\Table\Selection
     */
    public function findLimitedTopDonators($limit)
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1)) AS total_paid,
            SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 2), \'-\', -1)) AS `total_points`,
            '. self::COLUMN_ACCOUNT)
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->group(self::COLUMN_ACCOUNT)
            ->limit($limit);
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function findGrouppedPaidDonations()
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1) AS paid,
            COUNT(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1)) AS count')
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->group('paid');
    }

    /**
     * @param $months
     * @return \Nette\Database\Table\Selection
     */
    public function findGrouppedYearAndMonthDonations($months)
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('YEAR(FROM_UNIXTIME(' . self::COLUMN_TIME . ')) AS year,
            LPAD(MONTH(FROM_UNIXTIME(' . self::COLUMN_TIME . ')), 2, 0) AS month,
            SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1)) AS total_paid')
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->where('FROM_UNIXTIME(' . self::COLUMN_TIME . ') > DATE_SUB(now(), INTERVAL ? MONTH)', $months)
            ->group('year, month');
    }

    /**
     * @param $account
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneDonateStatsByAccount($account)
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1)) AS `total_paid`,
            SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 2), \'-\', -1)) AS total_points')
            ->where(self::COLUMN_ACCOUNT, $account->id)
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->fetch();
    }

    /**
     * @param $account
     * @return \Nette\Database\Table\Selection
     */
    public function findAllDonatesByAccount($account)
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1) AS paid,
            SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1) AS points,
            ' . self::COLUMN_TIME)
            ->where(self::COLUMN_ACCOUNT, $account->id)
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->order(self::COLUMN_TIME . ' DESC');
    }

    /**
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function findOneTotalPaidAndPointsForDonation()
    {
        return $this->database->table(self::TABLE_NAME)
            ->select('SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 1), \'-\', -1)) AS total_paid,
            SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(' . self::COLUMN_COMMENT . ', \'-\', 2), \'-\', -1)) AS total_points')
            ->where(self::COLUMN_TYPE, self::TYPE_DONATE_DONATED)
            ->fetch();
    }

    /**
     * Store log record
     *
     * @param $account
     * @param $ip
     * @param $type
     * @param null $comment
     */
    public function addLog($account, $ip, $type, $comment = NULL)
    {
        $this->database->table(self::TABLE_NAME)->insert(array(
            self::COLUMN_ACCOUNT => $account->id,
            self::COLUMN_IP => $ip,
            self::COLUMN_TYPE => $type,
            self::COLUMN_COMMENT => $comment,
            self::COLUMN_TIME => time()
        ));
    }
}