<?php

namespace Pagarme\Core\Test\Recurrence\Repositories;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Factories\RepetitionFactory;
use Pagarme\Core\Recurrence\Repositories\RepetitionRepository;
use Pagarme\Core\Test\Abstractions\AbstractRepositoryTest;

class RepetitionRepositoryTest extends AbstractRepositoryTest
{
    public function getRepository()
    {
        return new RepetitionRepository();
    }

    public function testShouldReturnARepetitionByProductId()
    {
        $this->insertRepetition();
        $this->assertCount(1, $this->repo->findBySubscriptionId(23));
    }

    public function testShouldNotReturnARepetitionByProductId()
    {
        $this->assertCount(0, $this->repo->findBySubscriptionId(23));
    }

    public function testShouldFindARepetitionById()
    {
        $repetition = $this->insertRepetition();

        $repetitionFound = $this->repo->find($repetition->getId());

        $this->assertInstanceOf(Repetition::class, $repetitionFound);
        $this->assertTrue($repetitionFound->equals($repetitionFound));
    }

    public function testShouldReturnNullIfNotFoundARepetition()
    {
        $this->assertNull($this->repo->find(30));
    }

    public function testShouldSaveARepetition()
    {
        $repetition = [
            "subscription_id" => "32",
            "interval_count" => 1,
            "interval" => "month",
            "recurrence_price"=> 50000,
            "cycles"=> 5
        ];

        $factory = new RepetitionFactory();
        $repetitionEntity = $factory->createFromPostData($repetition);
        $this->repo->save($repetitionEntity);

        $repetitionFound = $this->repo->find($repetitionEntity->getId());

        $this->assertInstanceOf(Repetition::class, $repetitionFound);
    }

    public function testShouldUpdateARepetition()
    {
        $repetition = $this->insertRepetition();

        $repetition->setRecurrencePrice(2000);
        $repetition->setIntervalCount(5);

        $this->repo->save($repetition);

        $repetitionUpdated = $this->repo->find($repetition->getId());

        $this->assertEquals(2000, $repetitionUpdated->getRecurrencePrice());
        $this->assertEquals(5, $repetitionUpdated->getIntervalCount());
    }

    public function testShouldDeleteARepetition()
    {
        $repetition = $this->insertRepetition();
        $this->repo->delete($repetition);

        $this->assertEmpty($this->repo->find($repetition->getId()));
    }

    public function testShouldDeleteARepetitionByProductSubscriptionId()
    {
        $this->insertRepetition();
        $repetition = $this->insertRepetition();

        $this->assertCount(2, $this->repo->findBySubscriptionId(23));

        $this->repo->deleteBySubscriptionId($repetition->getSubscriptionId());

        $this->assertCount(0, $this->repo->findBySubscriptionId(23));
    }

    public function testShouldReturnARepetitionSearchByPagarmeId()
    {
        $mockAbstractString = $this->createMock(AbstractValidString::class);
        $this->assertNull($this->repo->findByPagarmeId($mockAbstractString), "Method not implemented");
    }

    public function testShouldListAllRepetitions()
    {
        $this->assertNull($this->repo->listEntities( 10, false), "Method not implemented");
    }

    private function insertRepetition()
    {
        $repetition = [
            "subscription_id" => "23",
            "interval_count" => 1,
            "interval" => "month",
            "recurrence_price"=> 50000,
            "cycles"=> 5
        ];

        $factory = new RepetitionFactory();
        $repetitionEntity = $factory->createFromPostData($repetition);

        $this->repo->save($repetitionEntity);

        return $repetitionEntity;
    }
}