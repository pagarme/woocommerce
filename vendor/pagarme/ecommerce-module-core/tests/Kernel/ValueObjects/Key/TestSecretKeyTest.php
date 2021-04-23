<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Key;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TestSecretKeyTest extends TestCase
{
    const VALID1 = 'sk_test_xxxxxxxxxxxxxxxx';
    const VALID2 = 'sk_test_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Key\TestSecretKey
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTestSecretKeyShouldAcceptOnlyValidTestSecretKeys()
    {
        $this->doValidStringTest();
    }
}