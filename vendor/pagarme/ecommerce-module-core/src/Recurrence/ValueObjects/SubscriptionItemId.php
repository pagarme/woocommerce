<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class SubscriptionItemId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^si_\w{16}$/', $value ?? '') === 1;
    }
}
