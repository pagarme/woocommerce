<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Key;

use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class HubAccessTokenKeyTest extends TestCase
{
    const VALID1 = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    const VALID2 = 'yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anHubAccessTokenKeyShouldAcceptOnlyValidHubAccessTokenKeys()
    {
        $this->doValidStringTest();
    }
}
