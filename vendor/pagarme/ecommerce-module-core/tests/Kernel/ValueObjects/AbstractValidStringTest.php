<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Test\Mock\ValidStringMock;
use PHPUnit\Framework\TestCase;

class AbstractValidStringTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     *
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function aValidStringShouldBeValidateable()
    {
        $mock = new ValidStringMock(ValidStringMock::VALID1);
        $this->assertEquals(ValidStringMock::VALID1, $mock->getValue());

        $this->expectException(InvalidParamException::class);
        $mock = new ValidStringMock(ValidStringMock::INVALID);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aValidStringShouldBeComparable()
    {
        $mock11 = new ValidStringMock(ValidStringMock::VALID1);
        $mock12 = new ValidStringMock(ValidStringMock::VALID1);
        $mock2 = new ValidStringMock(ValidStringMock::VALID2);

        $this->assertTrue($mock11->equals($mock12));
        $this->assertFalse($mock11->equals($mock2));
        $this->assertFalse($mock12->equals($mock2));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     */
    public function aValidStringShouldBeJsonSerializable()
    {
        $mock = new ValidStringMock(ValidStringMock::VALID1);

        $json = json_encode($mock);
        $expected = json_encode(ValidStringMock::VALID1);

        $this->assertEquals($expected, $json);
    }
}
