<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.09.2017
 * Time: 20:38
 */

namespace App\Model;

use Nette;

class DonateManager extends Nette\Object
{
    const
        VIP_PRICE = 10;

    const
        PRODUCT_TABLE_NAME = 'donate_products',
        PRODUCT_COLUMN_ID = 'id',
        PRODUCT_COLUMN_COINS = 'coins',
        PRODUCT_COLUMN_BONUS_COINS = 'bonus_coins',
        PRODUCT_COLUMN_PRICE = 'price';

    const
        ALIAS_TOTAL_PAID = 'total_paid',
        ALIAS_TOTAL_POINTS = 'total_points',
        ALIAS_PAID = 'paid',
        ALIAS_POINTS = 'points',
        ALIAS_COUNT = 'count';

    /** @var Nette\Database\Context */
    private $database;

    /** @var LogRepository */
    private $logRepository;

    public function __construct(Nette\Database\Context $database, LogRepository $logRepository)
    {
        $this->database = $database;
        $this->logRepository = $logRepository;
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->database->table(self::PRODUCT_TABLE_NAME)
            ->order(self::PRODUCT_COLUMN_COINS . ' ASC, ' . self::PRODUCT_COLUMN_BONUS_COINS . ' ASC');
    }

    /**
     * @param $id
     * @return Nette\Database\Table\IRow
     */
    public function findOneById($id)
    {
        return $this->database->table(self::PRODUCT_TABLE_NAME)->get($id);
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function findTop10Donators()
    {
        return $this->logRepository->findLimitedTopDonators(10);
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function findPaidStats()
    {
        return $this->logRepository->findGrouppedPaidDonations();
    }

    /**
     * @param $months
     * @return Nette\Database\Table\Selection
     */
    public function findLastMonthsStats($months)
    {
        return $this->logRepository->findGrouppedYearAndMonthDonations($months);
    }

    /**
     * @param $account
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getDonateStatsForAccount($account)
    {
        return $this->logRepository->findOneDonateStatsByAccount($account);
    }

    /**
     * @param $account
     * @return Nette\Database\Table\Selection
     */
    public function findAllDonatesForAccount($account)
    {
        return $this->logRepository->findAllDonatesByAccount($account);
    }

    /**
     * @return bool|mixed|Nette\Database\Table\IRow
     */
    public function getTotalDistributionStats()
    {
        return $this->logRepository->findOneTotalPaidAndPointsForDonation();
    }

    /**
     * @param $price
     * @param $coins
     * @param $bonus_coins
     * @return bool|int|Nette\Database\Table\IRow
     */
    public function addDonateProduct($price, $coins, $bonus_coins)
    {
        return $this->database->table(self::PRODUCT_TABLE_NAME)->insert([
            self::PRODUCT_COLUMN_PRICE => $price,
            self::PRODUCT_COLUMN_COINS => $coins,
            self::PRODUCT_COLUMN_BONUS_COINS => $bonus_coins
        ]);
    }

    /**
     * @param $id
     * @param $price
     * @param $coins
     * @param $bonus_coins
     * @return bool
     */
    public function updateDonateProduct($id, $price, $coins, $bonus_coins)
    {
        $product = $this->findOneById($id);

        if(!$product)
            return false;

        $product->update([
            self::PRODUCT_COLUMN_PRICE => $price,
            self::PRODUCT_COLUMN_COINS => $coins,
            self::PRODUCT_COLUMN_BONUS_COINS => $bonus_coins
        ]);

        return true;
    }
}