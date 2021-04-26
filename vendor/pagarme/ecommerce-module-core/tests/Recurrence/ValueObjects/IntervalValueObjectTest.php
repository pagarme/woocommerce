<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use PHPUnit\Framework\TestCase;

class IntervalValueObjectTest extends TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Call to undefined method Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject::hour()
     */
    public function testShouldReturnAExceptionBecauseTheTypeNotExist()
    {
        IntervalValueObject::hour(2);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Interval count should be greater than 0: -10!
     */
    public function testShouldReturnAExceptionBecauseTheValueIsWrong()
    {
        IntervalValueObject::month(-10);
    }

    public function testShoudReturnAIntervalWithTypeMonthAndCountTwo()
    {
        $interval = IntervalValueObject::month(2);
        $this->assertEquals(2, $interval->getIntervalCount());
        $this->assertEquals("month", $interval->getIntervalType());
    }

    public function testShoudReturnAIntervalWithTypeWeekAndCountTwo()
    {
        $interval = IntervalValueObject::week(2);
        $this->assertEquals(2, $interval->getIntervalCount());
        $this->assertEquals("week", $interval->getIntervalType());
    }

    public function testShoudReturnAIntervalWithTypeYearAndCountTwo()
    {
        $interval = IntervalValueObject::year(2);
        $this->assertEquals(2, $interval->getIntervalCount());
        $this->assertEquals("year", $interval->getIntervalType());
    }

    public function testShoudReturnAIntervalWithTypeDayAndCountTwo()
    {
        $interval = IntervalValueObject::day(2);
        $this->assertEquals(2, $interval->getIntervalCount());
        $this->assertEquals("day", $interval->getIntervalType());
    }

    public function testShoudReturnTrueBecauseTheObjectsAreEquals()
    {
        $interval = IntervalValueObject::day(2);
        $interval2 = IntervalValueObject::day(2);
        $this->assertTrue($interval->equals($interval2));
    }

    public function testShoudReturnAnJsonWhenCallJsonEncodeMethod()
    {
        $interval = IntervalValueObject::day(2);
        $this->assertJson(json_encode($interval));
    }
}