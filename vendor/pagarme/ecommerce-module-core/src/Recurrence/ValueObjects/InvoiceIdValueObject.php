<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class InvoiceIdValueObject extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/in_\w{16}$/', $value ?? '') === 1;
    }
}