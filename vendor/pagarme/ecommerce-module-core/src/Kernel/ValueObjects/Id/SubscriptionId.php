<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class SubscriptionId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^sub_\w{16}$/', $value ?? '') === 1;
    }
}
