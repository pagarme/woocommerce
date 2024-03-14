<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

final class TestSecretKey extends AbstractSecretKey
{
    protected function validateValue($value)
    {
        return preg_match('/^sk_test_\w{16}$/', $value ?? '') === 1;
    }
}