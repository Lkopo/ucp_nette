<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 05.03.2017
 * Time: 12:48
 */

namespace App\Latte;

/**
 * Converts status int value into colorized text
 *
 * Class StatusFilter
 * @package App\Latte
 */
class StatusFilter extends FilterHelper
{
    /**
     * @param $status
     * @return string
     */
    public function __invoke($status)
    {
        if((int) $status == 1)
            return '<span class="status_online">Online</span>';
        else
            return '<span class="status_offline">Offline</span>';
    }
}