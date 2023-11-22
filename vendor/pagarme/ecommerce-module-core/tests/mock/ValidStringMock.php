<?php

namespace Pagarme\Core\Test\Mock;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;

class ValidStringMock extends AbstractValidString
{
    const VALID1 = 'mock1';
    const VALID2 = 'mock2';
    const VALIDATION_REGEX = '/^mock\d$/';

    const INVALID = 'itisnotavalidmockvalueforthisclas';

    protected function validateValue($value)
    {
        return preg_match(self::VALIDATION_REGEX, $value ?? '') === 1;
    }
}
