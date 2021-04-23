<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Key;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class TestPublicKeyTest extends TestCase
{
    const VALID1 = 'pk_test_xxxxxxxxxxxxxxxx';
    const VALID2 = 'pk_test_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anTestPublicKeyShouldAcceptOnlyValidTestPublicKeys()
    {
        $this->doValidStringTest();
    }
}