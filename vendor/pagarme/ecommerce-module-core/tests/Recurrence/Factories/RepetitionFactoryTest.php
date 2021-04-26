<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Factories\RepetitionFactory;
use PHPUnit\Framework\TestCase;

class RepetitionFactoryTests extends TestCase
{
    public function testCreateFromPostDataShouldReturnARepetition()
    {
        $repetitionFactory = new RepetitionFactory();

        $data = [
            'id' => 456654,
            'subscription_id' => 12345,
            'interval' => 'month',
            'interval_count' => 5,
            'recurrence_price' => 50000,
            'cycles' => 5,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $repetition = $repetitionFactory->createFromPostData($data);

        $this->assertInstanceOf(Repetition::class, $repetition);
        $this->assertEquals($data['id'], $repetition->getId());
        $this->assertEquals($data['subscription_id'], $repetition->getSubscriptionId());
        $this->assertEquals($data['interval'], $repetition->getInterval());
        $this->assertEquals($data['interval_count'], $repetition->getIntervalCount());
        $this->assertEquals($data['recurrence_price'], $repetition->getRecurrencePrice());
        $this->assertEquals($data['cycles'], $repetition->getCycles());
        $this->assertEquals($data['created_at'], $repetition->getCreatedAt());
        $this->assertEquals($data['updated_at'], $repetition->getUpdatedAt());
    }

    public function testCreateFromPostDataShouldReturnAnEmptyRepetition()
    {
        $repetitionFactory = new RepetitionFactory();

        $repetition = $repetitionFactory->createFromPostData([]);

        $this->assertInstanceOf(Repetition::class, $repetition);
        $this->assertEmpty($repetition->getId());
        $this->assertEmpty($repetition->getSubscriptionId());
        $this->assertEmpty($repetition->getInterval());
        $this->assertEmpty($repetition->getIntervalCount());
        $this->assertEmpty($repetition->getRecurrencePrice());
        $this->assertEmpty($repetition->getCreatedAt());
        $this->assertEmpty($repetition->getUpdatedAt());
    }

    public function testShouldNotReturnARepetitionObjectIfDontPassAnArrayToFactory()
    {
        $repetitionFactory = new RepetitionFactory();

        $repetition = $repetitionFactory->createFromPostData("");
        $this->assertNotInstanceOf(Repetition::class, $repetition);
    }

    public function testCreateFromDbShouldReturnARepetition()
    {
        $repetitionFactory = new RepetitionFactory();

        $dbData = [
            'id' => 456654,
            'subscription_id' => 12345,
            'interval' => 'month',
            'interval_count' => 5,
            'recurrence_price' => 50000,
            'cycles' => 5,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $repetition = $repetitionFactory->createFromDbData($dbData);

        $this->assertInstanceOf(Repetition::class, $repetition);
        $this->assertEquals($dbData['id'], $repetition->getId());
        $this->assertEquals($dbData['subscription_id'], $repetition->getSubscriptionId());
        $this->assertEquals($dbData['interval'], $repetition->getInterval());
        $this->assertEquals($dbData['interval_count'], $repetition->getIntervalCount());
        $this->assertEquals($dbData['recurrence_price'], $repetition->getRecurrencePrice());
        $this->assertEquals($dbData['cycles'], $repetition->getCycles());
        $this->assertEquals($dbData['created_at'], $repetition->getCreatedAt());
        $this->assertEquals($dbData['updated_at'], $repetition->getUpdatedAt());
    }
}
