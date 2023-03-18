<?php
/**
 * Created by PhpStorm.
 * User: Lkopo
 * Date: 02.03.2017
 * Time: 16:29
 */

namespace App\Latte;

use Nette;

abstract class FilterHelper extends Nette\Object
{
    /** @var Nette\Http\Request */
    public $httpRequest;

    public $basePath;

    public function __construct(Nette\Http\Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        $this->basePath = $this->httpRequest->getUrl()->getBasePath();
    }
}