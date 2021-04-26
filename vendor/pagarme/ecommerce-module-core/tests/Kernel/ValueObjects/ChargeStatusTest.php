<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use PHPUnit\Framework\TestCase;

class ChargeStatusTest extends TestCase
{
    /**
     *
     * For the valid charge statuses list, check
     * ({@link https://docs.mundipagg.com/v1/reference#cobran%C3%A7as})
     *
     */
    protected $validStatuses = [
        'paid',
        'pending',
        'canceled',
        'processing',
        'failed',
        'underpaid',
        'overpaid'
    ];

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\ChargeStatus
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aChargeStatusShouldBeComparable()
    {
        $ChargeStatusPaid1 = ChargeStatus::paid();
        $ChargeStatusPaid2 = ChargeStatus::paid();

        $ChargeStatusPending2 = ChargeStatus::pending();

        $this->assertTrue($ChargeStatusPaid1->equals($ChargeStatusPaid2));
        $this->assertFalse($ChargeStatusPaid1->equals($ChargeStatusPending2));
        $this->assertFalse($ChargeStatusPaid2->equals($ChargeStatusPending2));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\ChargeStatus
     */
    public function aChargeStatusShouldBeJsonSerializable()
    {
        $ChargeStatusPaid1 = ChargeStatus::paid();

        $json = json_encode($ChargeStatusPaid1);
        $expected = json_encode(ChargeStatus::PAID);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\ChargeStatus
     */
    public function allChargeStatusConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $ChargeStatusPaid = ChargeStatus::paid();

        $reflectionClass = new \ReflectionClass($ChargeStatusPaid);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $brand) {
            $ChargeStatus = ChargeStatus::$brand();
            $this->assertEquals($brand, $ChargeStatus->getStatus());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\ChargeStatus
     */
    public function aInvalidChargeStatusShouldNotBeInstantiable()
    {
        $ChargeStatusClass = ChargeStatus::class;
        $invalidChargeStatus = ChargeStatus::PAID . ChargeStatus::PAID;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$ChargeStatusClass}::{$invalidChargeStatus}()");

        $ChargeStatusPaid = ChargeStatus::$invalidChargeStatus();
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\ChargeStatus
     */
    public function aChargeStatusShouldAcceptAllPossibleChargeStatuses()
    {
        foreach ($this->validStatuses as $validStatus) {
            $chargeStatus = ChargeStatus::$validStatus();

            $this->assertEquals($validStatus, $chargeStatus->getStatus());
        }
    }
}
