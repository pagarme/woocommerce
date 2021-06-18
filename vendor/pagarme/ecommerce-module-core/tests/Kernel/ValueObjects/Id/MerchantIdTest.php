<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class MerchantIdTest extends TestCase
{
    const VALID1 = 'merch_xxxxxxxxxxxxxxxx';
    const VALID2 = 'merch_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Id\MerchantId
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anMerchantIdShouldAcceptOnlyValidMerchantIds()
    {
        $this->doValidStringTest();
    }
}
