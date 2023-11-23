<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class CustomerId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^cus_\w{16}$/', $value ?? '') === 1;
    }
}