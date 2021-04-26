<?php

namespace Pagarme\Core\Kernel\Exceptions;

class InvalidOperationException extends AbstractPagarmeCoreException
{
    public function construct($message)
    {
        parent::__construct($message, 400);
    }
}