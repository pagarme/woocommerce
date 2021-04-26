<?php

namespace Pagarme\Core\Test\Recurrence\ValueObjects;

use Pagarme\Core\Test\Kernel\ValueObjects\ValidStringTestTrait;
use PHPUnit\Framework\TestCase;

class PlanIdTest extends TestCase
{
    const VALID1 = 'plan_xxxxxxxxxxxxxxxx';
    const VALID2 = 'plan_yyyyyyyyyyyyyyyy';

    const INVALID = self::VALID1 . self::VALID2;

    use ValidStringTestTrait;

    /**
     * @test
     *
     * @covers \Pagarme\Core\Recurrence\ValueObjects\PlanId
     *
     * @uses   \Pagarme\Core\Kernel\ValueObjects\AbstractValidString
     * @uses   \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function aPlanIdShouldAcceptOnlyValidChargeIds()
    {
        $this->doValidStringTest();
    }
}
