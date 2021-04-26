<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryCreateFromPostDataInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);
}