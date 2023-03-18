<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 17:16
 */

namespace App\Latte;

/**
 * Changes money into golds
 *
 * Class ToGoldsFilter
 * @package App\Latte
 */
class ToGoldsFilter extends FilterHelper
{
    /**
     * @param $money
     * @return float
     */
    public function __invoke($money)
    {
        return round($money / 10000);
    }
}