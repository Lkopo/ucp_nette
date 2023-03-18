<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 08.10.2017
 * Time: 14:53
 */

namespace App\Latte;

use Nette\Utils\Strings;

/**
 * Converts string format to ASCII format
 * Ex. zlaté časy => zlate casy
 *
 * uses Strings library
 *
 * Class ToAsciiFilter
 * @package App\Latte
 */
class ToAsciiFilter extends FilterHelper
{
    /**
     * @param $str
     * @return string
     */
    public function __invoke($str)
    {
        return Strings::toAscii($str);
    }
}