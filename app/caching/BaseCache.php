<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 10.03.2017
 * Time: 14:47
 */

namespace App\Caching;

use Nette;

abstract class BaseCache extends Nette\Object
{
    /** @var Nette\Caching\Cache */
    protected $cache;

    public function __construct($path)
    {
        $storage = new Nette\Caching\Storages\FileStorage('temp');
        $this->cache = new Nette\Caching\Cache($storage, $path);
    }

    /**
     * @return Nette\Caching\Cache
     */
    public function getCache()
    {
        return $this->cache;
    }
}