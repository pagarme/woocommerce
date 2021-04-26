<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use PHPUnit\Framework\TestCase;

class NumericStringTest extends TestCase
{

    const VALID1 = '1234';
    const VALID2 = 1345;

    const INVALID = '13notanumber45';

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\NumericString
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function aNumericStringShouldAcceptOnlyNumbers()
    {
        $this->doValidStringTest();
    }
}
