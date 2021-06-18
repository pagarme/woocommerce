<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

interface FactoryInterface extends
    FactoryCreateFromDbDataInterface,
    FactoryCreateFromPostDataInterface
{
    /**
     *
     * @param  array $postData
     * @return AbstractEntity
     */
    public function createFromPostData($postData);

    /**
     *
     * @param  array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData);
}
