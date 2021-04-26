<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\InvoiceState;
use PHPUnit\Framework\TestCase;

class InvoiceStateTest extends TestCase
{
    protected $validStates = [
        'paid',
        'canceled',
    ];

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\InvoiceState
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aInvoiceStateShouldBeComparable()
    {
        $InvoiceStatePaid1 = InvoiceState::paid();
        $InvoiceStatePaid2 = InvoiceState::paid();

        $InvoiceStateCanceled = InvoiceState::canceled();

        $this->assertTrue($InvoiceStatePaid1->equals($InvoiceStatePaid2));
        $this->assertFalse($InvoiceStatePaid1->equals($InvoiceStateCanceled));
        $this->assertFalse($InvoiceStatePaid2->equals($InvoiceStateCanceled));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\InvoiceState
     */
    public function aInvoiceStateShouldBeJsonSerializable()
    {
        $InvoiceStatePaid1 = InvoiceState::paid();

        $json = json_encode($InvoiceStatePaid1);
        $expected = json_encode(InvoiceState::PAID);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\InvoiceState
     */
    public function allInvoiceStateConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $InvoiceStatePaid = InvoiceState::paid();

        $reflectionClass = new \ReflectionClass($InvoiceStatePaid);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $state) {
            $InvoiceState = InvoiceState::$state();
            $this->assertEquals($state, $InvoiceState->getState());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\InvoiceState
     */
    public function aInvalidInvoiceStateShouldNotBeInstantiable()
    {
        $InvoiceStateClass = InvoiceState::class;
        $invalidInvoiceState = InvoiceState::PAID . InvoiceState::PAID;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$InvoiceStateClass}::{$invalidInvoiceState}()");

        $InvoiceStatePaid = InvoiceState::$invalidInvoiceState();
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\InvoiceState
     */
    public function aInvoiceStateShouldAcceptAllPossibleInvoiceStates()
    {
        foreach ($this->validStates as $validStatus) {
            $invoiceState = InvoiceState::$validStatus();

            $this->assertEquals($validStatus, $invoiceState->getState());
        }
    }
}
