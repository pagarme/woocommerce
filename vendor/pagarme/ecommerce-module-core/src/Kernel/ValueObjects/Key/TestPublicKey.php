<?php

namespace Pagarme\Core\Kernel\ValueObjects\Key;

final class TestPublicKey extends AbstractPublicKey
{
    protected function validateValue($value)
    {
        return preg_match('/^pk_test_\w{16}$/', $value ?? '') === 1;
    }
}