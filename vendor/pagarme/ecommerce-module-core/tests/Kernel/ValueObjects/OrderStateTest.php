<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\ValueObjects\OrderState;
use PHPUnit\Framework\TestCase;

class OrderStateTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\OrderState
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aOrderStateShouldBeComparable()
    {
        $OrderStateComplete1 = OrderState::complete();
        $OrderStateComplete2 = OrderState::complete();

        $OrderStateClosed = OrderState::closed();

        $this->assertTrue($OrderStateComplete1->equals($OrderStateComplete2));
        $this->assertFalse($OrderStateComplete1->equals($OrderStateClosed));
        $this->assertFalse($OrderStateComplete2->equals($OrderStateClosed));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\OrderState
     */
    public function aOrderStateShouldBeJsonSerializable()
    {
        $OrderStateComplete1 = OrderState::complete();

        $json = json_encode($OrderStateComplete1);
        $expected = json_encode(OrderState::COMPLETE);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\OrderState
     */
    public function allOrderStateConstantsDefinedInTheClassShouldBeInstantiable()
    {
        $OrderStateComplete = OrderState::complete();

        $reflectionClass = new \ReflectionClass($OrderStateComplete);
        $constants = $reflectionClass->getConstants();

        foreach ($constants as $const => $state) {
            $const = strtolower($const);
            $const = explode('_', $const ?? '');
            foreach ($const as &$c) {
                $c = ucfirst($c);
            }
            $const = implode('', $const);
            $const = lcfirst($const);

            $OrderState = OrderState::$const();
            $this->assertEquals($state, $OrderState->getState());
        }
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\OrderState
     */
    public function aInvalidOrderStateShouldNotBeInstantiable()
    {
        $OrderStateClass = OrderState::class;
        $invalidOrderState = OrderState::COMPLETE . OrderState::COMPLETE;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Call to undefined method {$OrderStateClass}::{$invalidOrderState}()");

        $OrderStateComplete = OrderState::$invalidOrderState();
    }
}
