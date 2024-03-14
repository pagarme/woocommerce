<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

trait ValidStringTestTrait
{
    protected function doValidStringTest()
    {
        $class = self::class;
        $class = substr($class, 0, strlen($class) -4);
        $class = str_replace('\\Test\\', '\\', $class ?? '');

        $validStringObject = new $class(self::VALID1);
        $this->assertEquals(self::VALID1, $validStringObject->getValue());

        $validStringObject = new $class(self::VALID2);
        $this->assertEquals(self::VALID2, $validStringObject->getValue());

        $this->expectException(InvalidParamException::class);
        $validStringObject = new $class(self::INVALID);
    }
}