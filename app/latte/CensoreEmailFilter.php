<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 05.03.2017
 * Time: 16:46
 */

namespace App\Latte;

/**
 * Censore e-mail address (50% left side, 50% right side)
 *
 * Class CensoreEmailFilter
 * @package App\Latte
 */
class CensoreEmailFilter extends FilterHelper
{
    /**
     * @param $email
     * @return string
     */
    public function __invoke($email)
    {
        return substr($email, 0, intval($at_pos = strpos($email,'@'))/2) . str_repeat('*', $at_pos - intval($at_pos / 2)) . substr($email, $at_pos, round($diff = (strlen($email) - $at_pos)/2)) . str_repeat('*', intval($diff));
    }
}