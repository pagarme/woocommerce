<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class InvoiceIdTest extends TestCase
{
    const VALID1 = 'in_xxxxxxxxxxxxxxxx';
    const VALID2 = 'in_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    public function testeAnOrderIdShouldAcceptOnlyValidOrderIds()
    {
        $this->doValidStringTest();
    }
}
