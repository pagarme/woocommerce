<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class GUID extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/^\w{8}-(\w{4}-){3}\w{12}$/', $value ?? '') === 1;
    }
}