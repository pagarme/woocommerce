<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class CycleId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^cycle_\w{16}$/', $value ?? '') === 1;
    }
}
