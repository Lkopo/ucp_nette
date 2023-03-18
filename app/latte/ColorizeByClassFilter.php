<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 15:30
 */

namespace App\Latte;

/**
 * Colorize character name by its class
 *
 * Class ColorizeByClass
 * @package App\Latte
 */
class ColorizeByClassFilter extends FilterHelper
{
    private $class_colors = [
        1 => '#C79C6E', // Warrior
        2 => '#F58CBA', // Paladin
        3 => '#ABD473', // Hunter
        4 => '#FFF569', // Rogue
        5 => '#FFFFFF', // Priest
        6 => '#C41F3B', // Death Knight
        7 => '#0070DE', // Shaman
        8 => '#69CCF0', // Mage
        9 => '#9482C9', // Warlock
        11 => '#FF7D0A' // Druid
    ];

    /**
     * @param $name
     * @param $class
     * @return string
     */
    public function __invoke($name, $class)
    {
        $styles = array();
        //$styles[] = 'font-weight: bold';
        if(array_key_exists($class, $this->class_colors))
            $styles[] = 'color: ' . $this->class_colors[$class];

        return '<span class="character-name" style="' . implode('; ', $styles) . '">' . $name . '</span>';
    }
}