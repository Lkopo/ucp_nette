<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 16:07
 */

namespace App\Latte;

/**
 * Change character's race into icon
 *
 * Class RaceIconFilter
 * @package App\Latte
 */
class RaceIconFilter extends FilterHelper
{
    private $race_names = [
        1 => 'Human',
        2 => 'Orc',
        3 => 'Dwarf',
        4 => 'Night Elf',
        5 => 'Undead',
        6 => 'Tauren',
        7 => 'Gnome',
        8 => 'Troll',
        10 => 'Blood Elf',
        11 => 'Draenei'
    ];

    /**
     * @param $race
     * @param $gender
     * @return string
     */
    public function __invoke($race, $gender)
    {
        return '<img src="' . $this->basePath . 'assets/images/c_icon/' . $race . '-' . $gender . '.gif"
        title="' . $this->race_names[$race] . ' '. ($gender == 1 ? 'Female' : 'Male') . '"
        alt="' . $this->race_names[$race] . ' '. ($gender == 1 ? 'Female' : 'Male') . '" />';
    }
}