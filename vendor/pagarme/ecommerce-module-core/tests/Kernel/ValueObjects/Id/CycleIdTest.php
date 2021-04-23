<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class CycleIdTest extends TestCase
{
    const VALID1 = 'cycle_xxxxxxxxxxxxxxxx';
    const VALID2 = 'cycle_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;


    public function testanOrderIdShouldAcceptOnlyValidOrderIds()
    {
        $this->doValidStringTest();
    }
}
