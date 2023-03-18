<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 16:19
 */

namespace App\Latte;

/**
 * Change character's class into icon
 *
 * Class ClassIconFilter
 * @package App\Latte
 */
class ClassIconFilter extends FilterHelper
{
    private $class_names = [
        1 => 'Warrior',
        2 => 'Paladin',
        3 => 'Hunter',
        4 => 'Rogue',
        5 => 'Priest',
        6 => 'Death Knight',
        7 => 'Shaman',
        8 => 'Mage',
        9 => 'Warlock',
        11 => 'Druid'
    ];

    /**
     * @param $class
     * @return string
     */
    public function __invoke($class)
    {
        return '<img src="' . $this->basePath . 'assets/images/c_icon/' . $class . '.gif"
        title="' . $this->class_names[$class] . '"
        alt="' . $this->class_names[$class] . '" />';
    }
}