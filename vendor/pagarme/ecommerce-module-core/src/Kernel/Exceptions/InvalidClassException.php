<?php

namespace Pagarme\Core\Kernel\Exceptions;

class InvalidClassException extends AbstractPagarmeCoreException
{
    public function __construct($actualClass, $expectedClass)
    {
        $message = "$actualClass is not a $expectedClass!";
        parent::__construct($message, 400, null);
    }
}