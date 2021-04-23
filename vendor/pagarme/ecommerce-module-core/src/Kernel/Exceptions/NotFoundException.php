<?php

namespace Pagarme\Core\Kernel\Exceptions;

class NotFoundException extends AbstractPagarmeCoreException
{
    public function __construct($message)
    {
        parent::__construct($message, 404, null);
    }
}