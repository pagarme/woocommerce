<?php

namespace Pagarme\Core\Kernel\Abstractions;

use Pagarme\Core\Kernel\Interfaces\PlatformCreditmemoInterface;

abstract class AbstractCreditmemoDecorator implements PlatformCreditmemoInterface
{
    protected $platformCreditmemo;

    public function __construct($platformCreditmemo = null)
    {
        $this->platformCreditmemo = $platformCreditmemo;
    }

    public function getPlatformCreditmemo()
    {
        return $this->platformCreditmemo;
    }
}