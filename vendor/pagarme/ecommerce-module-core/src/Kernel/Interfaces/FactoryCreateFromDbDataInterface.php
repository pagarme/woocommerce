<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromDbDataInterface
{
    /**
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData);
}
