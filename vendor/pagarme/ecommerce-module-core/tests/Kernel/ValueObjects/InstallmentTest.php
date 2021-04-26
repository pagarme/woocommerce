<?php

namespace Pagarme\Core\Test\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\ValueObjects\Installment;
use PHPUnit\Framework\TestCase;

class InstallmentTest extends TestCase
{
    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment
     *
     * @uses \Pagarme\Core\Kernel\Abstractions\AbstractValueObject
     *
     */
    public function aInstallmentShouldBeComparable()
    {
        $installment11 = new Installment(1,1,1);
        $installment12 = new Installment(1,1,1);

        $installment2 = new Installment(1,2,1);

        $this->assertTrue($installment11->equals($installment12));
        $this->assertTrue($installment12->equals($installment11));
        $this->assertFalse($installment11->equals($installment2));
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment
     */
    public function aInstallmentShouldBeJsonSerializable()
    {
        $base = new \stdClass();
        $base->times = 2;
        $base->baseTotal = 25;
        $base->interest = 0.3;
        $base->total = 32.5;
        $base->value = 16.25;

        $installment = new Installment(
            $base->times,
            $base->baseTotal,
            $base->interest
        );

        $json = json_encode($installment);
        $expected = json_encode($base);

        $this->assertEquals($expected, $json);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::setTimes()
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\Installment
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentTimesShouldBeBetween1And24()
    {
        for ($times = 1; $times <= 24; $times++) {
            $installment = new Installment(
                $times,
                1,
                0
            );

            $this->assertEquals($times, $installment->getTimes());
        }

        $tries = 20;
        $hits = 0;
        for($try = 0; $try < $tries; $try++) {
            try {
                $installment = new Installment(
                    rand(25, 100),
                    1,
                    0
                );
            } catch (InvalidParamException $e) {
                $hits++;
            }

            try {
                $installment = new Installment(
                    rand(-100000, 0),
                    1,
                    0
                );
            } catch (InvalidParamException $e) {
                $hits++;
            }
        }

        $this->assertEquals($tries*2, $hits);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::setBaseTotal()
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\Installment
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentBaseTotalShouldBeAtLeast0()
    {
        $valid = new Installment(1, 0, 0);
        $this->assertEquals(0, $valid->getBaseTotal());

        $valid= new Installment(1, 1, 0);
        $this->assertEquals(1, $valid->getBaseTotal());

        $this->expectException(InvalidParamException::class);
        $invalid = new Installment(1, -1, 0);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::setInterest()
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\Installment
     * @uses \Pagarme\Core\Kernel\Exceptions\InvalidParamException
     */
    public function installmentInterestShouldBeAtLeast0()
    {
        $valid = new Installment(1, 0, 0);
        $this->assertEquals(0, $valid->getInterest());

        $valid= new Installment(1, 0, 1);
        $this->assertEquals(1, $valid->getInterest());

        $this->expectException(InvalidParamException::class);
        $invalid = new Installment(1, 0, -1);
    }

    /**
     * @test
     *
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::getTimes
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::getBaseTotal
     * @covers \Pagarme\Core\Kernel\ValueObjects\Installment::getInterest
     *
     * @uses \Pagarme\Core\Kernel\ValueObjects\Installment
     */
    public function basePropertyGettersShouldReturnCorrectValues()
    {
        $valid = new Installment(1, 2, 3);

        $this->assertEquals(1, $valid->getTimes());
        $this->assertEquals(2, $valid->getBaseTotal());
        $this->assertEquals(3, $valid->getInterest());
    }
}
