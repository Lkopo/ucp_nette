<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 25.02.2017
 * Time: 15:36
 */

namespace App\Utils;

use Nette\Utils\Strings;

class TrinityCore
{
    /**
     * @param $username
     * @param $password
     * @return string
     */
    public static function createHash($username, $password) {
        return sha1(Strings::upper($username) . ":" . Strings::upper($password));
    }

    /**
     * @param $username
     * @param $password
     * @param $hash
     * @return bool
     */
    public static function verify($username, $password, $hash) {
        return self::createHash($username, $password) == $hash;
    }

    /**
     * @param array $vals
     * @return string
     */
    public static function createKey(array $vals) {
        $key = md5(uniqid(time()));

        // randomize order
        shuffle($vals);

        foreach ($vals as $val) $key = md5($key . $val);
        return $key;
    }
}