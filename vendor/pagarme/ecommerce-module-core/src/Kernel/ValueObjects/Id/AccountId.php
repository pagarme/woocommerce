<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class AccountId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^acc_\w{16}$/', $value ?? '') === 1;
    }
}