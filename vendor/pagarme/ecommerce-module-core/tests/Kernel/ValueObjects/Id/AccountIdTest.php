<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class AccountIdTest extends TestCase
{
    const VALID1 = 'acc_xxxxxxxxxxxxxxxx';
    const VALID2 = 'acc_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Id\AccountId
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anAccountIdShouldAcceptOnlyValidAccountIds()
    {
        $this->doValidStringTest();
    }
}
