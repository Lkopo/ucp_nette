<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 09.03.2017
 * Time: 17:09
 */

namespace App\Latte;

use Nette;

/**
 * Convert timestamp time to text format
 *
 * Class TimeToTextFilter
 * @package App\Latte
 */
class TimeToTextFilter extends FilterHelper
{
    /** @var \Kdyby\Translation\Translator */
    private $translator;

    public function __construct(Nette\Http\Request $httpRequest, \Kdyby\Translation\Translator $translator)
    {
        parent::__construct($httpRequest);
        $this->translator = $translator;
    }

    /**
     * @param $sec
     * @param bool $textual
     * @return string
     */
    public function __invoke($sec, $secs = false, $textual = true)
    {
        if($textual)
        {
            if($secs) {
                $div = array(2592000, 604800, 86400, 3600, 60, 1);
                $desc = array('pages.global.t_months', 'pages.global.t_weeks', 'pages.global.t_days_s',
                              'pages.global.t_hours_s', 'pages.global.t_mins_s', 'pages.global.t_secs_s');
            } else {
                $div = array(2592000, 604800, 86400, 3600, 60, /*1*/);
                $desc = array('pages.global.t_months', 'pages.global.t_weeks', 'pages.global.t_days_s',
                              'pages.global.t_hours_s', 'pages.global.t_mins_s', /*'pages.global.t_secs_s'*/);
            }

            $ret = null;
            foreach($div as $index => $value)
            {
                $quotent = floor($sec / $value); //greatest whole integer
                if($quotent > 0) {
                    $ret .= "$quotent{$this->translator->trans($desc[$index])}, ";
                    $sec %= $value;
                }
            }
            return substr($ret,0,-2);
        }
        else
        {
            $hours = floor ($sec / 3600);
            $sec -= $hours * 3600;
            $mins = floor ($sec / 60);
            $secs = $sec % 60;
            return $hours . ':' . $mins . ':' . $secs;
        }
    }
}