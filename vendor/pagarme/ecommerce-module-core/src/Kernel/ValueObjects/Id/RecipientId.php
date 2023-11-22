<?php

namespace Pagarme\Core\Kernel\ValueObjects\Id;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class RecipientId extends AbstractValidString
{
    protected function validateValue($value)
    {
        return (preg_match('/^re_\w{25}$/', $value ?? '') 
            || preg_match('/^rp_\w{16}$/', $value ?? '')) === true;
    }
}
