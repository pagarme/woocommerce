<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

final class SecretKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return preg_match('/^sk_\w{16}$/', $value ?? '') === 1;
    }
}