<?php

namespace Pagarme\Core\Test\Recurrence\Aggregates;

use Magento\Framework\Stdlib\DateTime;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use PHPUnit\Framework\TestCase;

class RepetitionTest extends TestCase
{
    private $repetition;

    protected function setUp(): void
    {
        $this->repetition = new Repetition();
    }

    /**
     * TODO: Change exception type to InvalidArgumentException
     * @expectedException \Exception
     * @expectedExceptionMessage  Recurrence price should be greater than 0: -10!
     */
    public function testShouldNotAddANegativePrice()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Recurrence price should be greater than 0: -10!");
        $this->repetition->setRecurrencePrice(-10);
    }

    public function testShouldSetCorrectRecurrencePrice()
    {
        $this->repetition->setRecurrencePrice(10);
        $this->assertEquals(10, $this->repetition->getRecurrencePrice());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Interval is not available! Passed value: hour
     */
    public function testShouldNotSetAnWrongIntervalType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Interval is not available! Passed value: hour");
        $this->repetition->setInterval("hour");
    }

    public function testShouldSetCorrectInterval()
    {
        $this->repetition->setInterval("month");
        $this->assertEquals("month", $this->repetition->getInterval());
    }

    public function testShouldReturnPluralIntervalLabel()
    {
        $this->repetition->setIntervalCount(10);
        $this->repetition->setInterval("month");

        $this->assertEquals("months", $this->repetition->getIntervalTypeLabel());
    }

    public function testShouldReturnSingularIntervalLabel()
    {
        $this->repetition->setIntervalCount(1);
        $this->repetition->setInterval("year");

        $this->assertEquals("year", $this->repetition->getIntervalTypeLabel());
    }

    public function testShouldReturnACompleteRepetition()
    {
        $id = 1;
        $subscriptionId = 10;
        $interval = 'month';
        $intervalCount = 2;
        $recurrencePrice = 50000;
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $this->repetition->setId($id);
        $this->repetition->setSubscriptionId($subscriptionId);
        $this->repetition->setInterval($interval);
        $this->repetition->setIntervalCount($intervalCount);
        $this->repetition->setRecurrencePrice($recurrencePrice);
        $this->repetition->setCreatedAt($createdAt);
        $this->repetition->setUpdatedAt($updatedAt);

        $this->assertEquals($id, $this->repetition->getId());
        $this->assertEquals($subscriptionId, $this->repetition->getSubscriptionId());
        $this->assertEquals($interval, $this->repetition->getInterval());
        $this->assertEquals($interval, $this->repetition->getIntervalType());
        $this->assertEquals($intervalCount, $this->repetition->getIntervalCount());
        $this->assertEquals($recurrencePrice, $this->repetition->getRecurrencePrice());
        $this->assertEquals($createdAt->format(Repetition::DATE_FORMAT), $this->repetition->getCreatedAt());
        $this->assertEquals($updatedAt->format(Repetition::DATE_FORMAT), $this->repetition->getUpdatedAt());
    }

    public function testShoudReturnJsonEncoded()
    {
        $id = 1;
        $subscriptionId = 10;
        $interval = 'month';
        $intervalCount = 2;
        $recurrencePrice = 50000;
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime();

        $this->repetition->setId($id);
        $this->repetition->setSubscriptionId($subscriptionId);
        $this->repetition->setInterval($interval);
        $this->repetition->setIntervalCount($intervalCount);
        $this->repetition->setRecurrencePrice($recurrencePrice);
        $this->repetition->setCreatedAt($createdAt);
        $this->repetition->setUpdatedAt($updatedAt);

        $this->assertJson(json_encode($this->repetition));
    }

    public function testShouldReturnFalseBecauseDoesntHasTheSameIntervalConfig()
    {
        $repetition = new Repetition();
        $repetition->setIntervalCount(2);
        $repetition->setInterval('month');

        $repetition2 = new Repetition();
        $repetition2->setIntervalCount(3);
        $repetition2->setInterval('year');

        $this->assertFalse($repetition->checkRepetitionIsCompatible($repetition2));
    }

    public function testShouldReturnTrueBecauseTheIntervalConfigAreCompatibles()
    {
        $repetition = new Repetition();
        $repetition->setIntervalCount(2);
        $repetition->setInterval('month');

        $repetition2 = new Repetition();
        $repetition2->setIntervalCount(2);
        $repetition2->setInterval('month');

        $this->assertTrue($repetition->checkRepetitionIsCompatible($repetition2));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Cycles should be greater than or equal to 0: -10!
     */
    public function testShouldNotAddANegativeCycle()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cycles should be greater than or equal to 0: -10!");
        $this->repetition->setCycles(-10);
    }

    public function testShouldSetCorrectCycle()
    {
        $this->repetition->setCycles(10);
        $this->assertEquals(10, $this->repetition->getCycles());
    }
}