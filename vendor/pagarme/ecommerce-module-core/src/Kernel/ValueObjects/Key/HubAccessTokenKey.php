<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

final class HubAccessTokenKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return preg_match('/^\w{64}$/', $value ?? '') === 1;
    }
}