<?php

namespace Pagarme\Core\Payment\ValueObjects;

final class CardToken extends AbstractCardIdentifier
{
    protected function validateValue($value)
    {
        return preg_match('/token_\w{16}$/', $value ?? '') === 1;
    }
}