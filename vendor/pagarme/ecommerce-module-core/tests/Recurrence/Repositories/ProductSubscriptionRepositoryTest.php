<?php

namespace Pagarme\Core\Test\Recurrence\Repositories;

use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Pagarme\Core\Recurrence\Repositories\ProductSubscriptionRepository;
use Pagarme\Core\Test\Abstractions\AbstractRepositoryTest;

class ProductSubscriptionRepositoryTest extends AbstractRepositoryTest
{
    public function testShouldReturnAProductSubscriptionByProductId()
    {
        $this->insertProductSubscription();
        $this->assertInstanceOf(ProductSubscription::class, $this->repo->findByProductId(23));
    }

    public function testShouldNotReturnAProductSubscription()
    {
        $this->assertNotInstanceOf(ProductSubscription::class, $this->repo->findByProductId(10));
        $this->assertEmpty($this->repo->findByProductId(10));
    }

    public function testShouldSaveAProductSubscription()
    {
        $product = [
            "product_id" => "32",
            "boleto" => true,
            "credit_card" => true,
            "allow_installments" => true,
            "sell_as_normal_product" => true,
            "repetitions" => [
                [
                    "interval_count" => 1,
                    "interval" => "month",
                    "recurrence_price"=> 50000,
                    "cycles" => 12
                ],
                [
                    "interval_count" => 2,
                    "interval" => "month",
                    "recurrence_price" => 45000
                ]
            ]
        ];

        $factory = new ProductSubscriptionFactory();
        $productSubscription = $factory->createFromPostData($product);
        $this->repo->save($productSubscription);

        $this->assertCount(1, $this->repo->listEntities(10, false));
    }

    public function testShouldDeleteAProductSubscriptionAndRepetitions()
    {
        $subscriptionProduct = $this->insertProductSubscription();
        $this->repo->delete($subscriptionProduct);

        $this->assertEmpty($this->repo->listEntities(10, false));
    }

    public function testShouldUpdateAProductSubscription()
    {
        $subscriptionProduct = $this->insertProductSubscription();

        $subscriptionProduct->setCreditCard(false);

        $this->repo->save($subscriptionProduct);

        $subscriptionProductUpdated = $this->repo->find($subscriptionProduct->getId());

        $this->assertFalse($subscriptionProductUpdated->getCreditCard());
    }

    public function testShouldUpdateAProductSubscriptionAndRepetitions()
    {
        $subscriptionProduct = $this->insertProductSubscription();

        $subscriptionProduct->setCreditCard(false);

        $repetition = (new Repetition)
            ->setSubscriptionId($subscriptionProduct->getId())
            ->setInterval("year")
            ->setIntervalCount(1)
            ->setRecurrencePrice(3000);

        $subscriptionProduct->addRepetition($repetition);
        $this->repo->save($subscriptionProduct);

        $subscriptionProductUpdated = $this->repo->find($subscriptionProduct->getId());

        $this->assertCount(3, $subscriptionProduct->getRepetitions());
        $this->assertFalse($subscriptionProductUpdated->getCreditCard());
    }

    public function testShouldFindAProductSubscriptionAndRepetitions()
    {
        $subscriptionProduct = $this->insertProductSubscription();

        $subscriptionProductFound = $this->repo->find($subscriptionProduct->getId());

        $this->assertInstanceOf(ProductSubscription::class, $subscriptionProductFound);
        $this->assertTrue($subscriptionProduct->equals($subscriptionProductFound));
    }

    public function testShouldReturnNullIfNotFoundAProductSubscription()
    {
        $this->assertNull($this->repo->find(30));
    }

    public function testShouldReturnAProductSubscriptionSearchByPagarmeId()
    {
        $mockAbstractString = $this->createMock(AbstractValidString::class);
        $this->assertNull($this->repo->findByPagarmeId($mockAbstractString), "Method not implemented");
    }

    private function insertProductSubscription()
    {
        $product = [
            "product_id" => "23",
            "boleto" => true,
            "credit_card" => true,
            "allow_installments" => true,
            "sell_as_normal_product" => true,
            "repetitions" => [
                [
                    "interval_count" => 1,
                    "interval" => "month",
                    "recurrence_price"=> 50000
                ],
                [
                    "interval_count" => 2,
                    "interval" => "month",
                    "recurrence_price" => 45000
                ]
		    ]
        ];

        $factory = new ProductSubscriptionFactory();
        $productSubscription = $factory->createFromPostData($product);

        $this->repo->save($productSubscription);

        return $productSubscription;
    }

    public function getRepository()
    {
        return new ProductSubscriptionRepository();
    }
}