<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class ChargeId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^ch_\w{16}$/', $value ?? '') === 1;
    }
}