<?php

namespace Pagarme\Core\Test\Recurrence\Repositories;

use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\Factories\SubProductFactory;
use Pagarme\Core\Recurrence\Repositories\SubProductRepository;
use Pagarme\Core\Test\Abstractions\AbstractRepositoryTest;

class SubProductRepositoryTest extends AbstractRepositoryTest
{
    public function testShouldSaveASubProduct()
    {
        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $subProductFactory = new SubProductFactory();

        $data = [
            'product_id' => 12345,
            'product_recurrence_id' => $recurrenceEntity->getId(),
            'recurrence_type' => 'plan',
            'name' => "Product Name",
            'description' => "Product Description",
            'quantity' => 5,
            'cycles' => 15,
            'price' => 3000,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $subProduct = $subProductFactory->createFromPostData($data);
        $this->repo->save($subProduct);

        $this->assertCount(1, $this->repo->findByRecurrence($recurrenceEntity));
    }

    public function testShouldReturnNullBecauseDoesntProductFromRecurrenceEntity()
    {
        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $this->assertEmpty($this->repo->findByRecurrence($recurrenceEntity));
    }

    public function testShouldFindASubProductByRecurrence()
    {
        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $subProductFactory = new SubProductFactory();

        $data = [
            'product_id' => 12345,
            'product_recurrence_id' => $recurrenceEntity->getId(),
            'recurrence_type' => 'plan',
            'name' => "Product Name",
            'description' => "Product Description",
            'quantity' => 5,
            'cycles' => 15,
            'price' => 3000,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $subProduct = $subProductFactory->createFromPostData($data);
        $this->repo->save($subProduct);

        $this->assertCount(1, $this->repo->findByRecurrence($recurrenceEntity));
    }

    public function testShouldUpdateASubProduct()
    {
        $subProduct = $this->insertSubProduct();

        $this->assertEquals(5, $subProduct->getQuantity());
        $this->assertEquals(15, $subProduct->getCycles());

        $subProduct->setQuantity(10);
        $subProduct->setCycles(24);

        $this->repo->save($subProduct);

        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $subProductUpdated = $this->repo->findByRecurrence($recurrenceEntity);

        $this->assertCount(1, $subProductUpdated);
        $this->assertEquals(12345, $subProductUpdated[0]->getProductId());
        $this->assertEquals(10, $subProductUpdated[0]->getQuantity());
        $this->assertEquals(24, $subProductUpdated[0]->getCycles());
    }

    public function testShouldDeleteASubProduct()
    {
        $subProduct = $this->insertSubProduct();

        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $this->assertCount(1, $this->repo->findByRecurrence($recurrenceEntity));

        $this->repo->delete($subProduct);

        $this->assertEmpty($this->repo->findByRecurrence($recurrenceEntity));
    }

    public function insertSubProduct()
    {
        $recurrenceEntity = new Plan();
        $recurrenceEntity->setId(1);

        $subProductFactory = new SubProductFactory();

        $data = [
            'product_id' => 12345,
            'product_recurrence_id' => $recurrenceEntity->getId(),
            'recurrence_type' => 'plan',
            'name' => "Product Name",
            'description' => "Product Description",
            'quantity' => 5,
            'cycles' => 15,
            'price' => 3000,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $subProduct = $subProductFactory->createFromPostData($data);
        $this->repo->save($subProduct);

        return $subProduct;
    }

    public function getRepository()
    {
        return new SubProductRepository();
    }
}