<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects\Id;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class GUIDTest extends TestCase
{
    const VALID1 = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
    const VALID2 = 'yyyyyyyy-yyyy-yyyy-yyyy-yyyyyyyyyyyy';
    
    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Id\GUID
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function anGUIDShouldAcceptOnlyValidGUIDs()
    {
        $this->doValidStringTest();
    }
}
