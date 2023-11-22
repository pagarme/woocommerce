<?php

namespace Pagarme\Core\Hub\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

final class HubInstallToken extends AbstractValidString
{
    protected function validateValue($value)
    {
        return preg_match('/\w{64}$/', $value ?? '') === 1;
    }
}
