<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TransactionIdTest extends TestCase
{
    const VALID1 = 'tran_xxxxxxxxxxxxxxxx';
    const VALID2 = 'tran_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Id\TransactionId
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTransactionIdShouldAcceptOnlyValidTransactionIds()
    {
        $this->doValidStringTest();
    }
}
