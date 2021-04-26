<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

interface SensibleDataInterface
{
    /**
     *
     * @param  string
     * @return string
     */
    public function hideSensibleData($string);
}