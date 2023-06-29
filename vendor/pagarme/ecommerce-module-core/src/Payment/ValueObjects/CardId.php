<?php

namespace Pagarme\Core\Payment\ValueObjects;

final class CardId extends AbstractCardIdentifier
{
    protected function validateValue($value)
    {
        return preg_match('/card_\w{16}$/', $value ?? '') === 1;
    }
}