<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class CustomerIdTest extends TestCase
{
    const VALID1 = 'cus_xxxxxxxxxxxxxxxx';
    const VALID2 = 'cus_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Id\CustomerId
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anCustomerIdShouldAcceptOnlyValidCustomerIds()
    {
        $this->doValidStringTest();
    }
}
