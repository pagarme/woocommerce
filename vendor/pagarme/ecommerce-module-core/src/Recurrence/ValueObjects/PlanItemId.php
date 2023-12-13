<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class PlanItemId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^pi_\w{16}$/', $value ?? '') === 1;
    }
}