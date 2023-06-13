<?php

namespace Pagarme\Core\Test\Recurrence\Services;

use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Pagarme\Core\Recurrence\Repositories\ProductSubscriptionRepository;
use Pagarme\Core\Recurrence\Services\ProductSubscriptionService;
use Pagarme\Core\Test\Abstractions\AbstractSetupTest;

class ProductSubscriptionServiceTest extends AbstractSetupTest
{
    /**
     * @var \Mockery\Mock
     */
    protected $service;

    public function setUp(): void
    {
        $logMock = \Mockery::mock(LogService::class);
        $logMock->shouldReceive('info')->andReturnTrue();

        $this->service = \Mockery::mock(ProductSubscriptionService::class)->makePartial();
        $this->service->shouldReceive('getLogService')->andReturn($logMock);

        parent::setUp();
    }

    public function testShouldReturnAllProductsSubscriptions()
    {
        $this->insertProductSubscription();
        $this->insertProductSubscription();

        $products = $this->service->findAll();
        $this->assertCount(2, $products);
    }

    public function testShouldReturnAProductsSubscriptionsById()
    {
        $product1 = $this->insertProductSubscription();
        $product2 = $this->insertProductSubscription();

        $productResult = $this->service->findById($product1->getId());
        $this->assertEquals($product1, $productResult);
        $this->assertNotEquals($product2, $productResult);
    }

    public function testShouldReturnAProductsSubscriptionsByProductId()
    {
        $product1 = $this->insertProductSubscription();
        $product2 = $this->insertProductSubscription();

        $productResult = $this->service->findByProductId($product1->getProductId());
        $this->assertEquals($product1, $productResult);
        $this->assertNotEquals($product2, $productResult);
    }


    public function testShouldDeleteAProductsSubscriptions()
    {
        $product1 = $this->insertProductSubscription();
        $this->insertProductSubscription();

        $products = $this->service->findAll();
        $this->assertCount(2, $products);

        $this->service->delete($product1->getId());

        $products = $this->service->findAll();
        $this->assertCount(1, $products);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Subscription Product not found - ID : 23
     */
    public function testShouldThrowAnExceptionBecauseTheRecurrenceProductIdNotExists()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Subscription Product not found - ID : 23");
        $this->service->delete(23);
    }

    public function testShouldSaveProductSubscriptionWithSuccess()
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

        $result = $this->service->saveProductSubscription($productSubscription);
        $this->assertEquals($productSubscription->getProductId(), $result->getProductId());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Product already exists on recurrence product- Product ID : 23
     */
    public function testShouldNotAllowSaveProductSubscriptionWithProductIdAlreadyExisting()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Product already exists on recurrence product- Product ID : 23");
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
        $productSubscription1 = $factory->createFromPostData($product);

        $this->service->saveProductSubscription($productSubscription1);

        $factory2 = new ProductSubscriptionFactory();
        $productSubscription2 = $factory2->createFromPostData($product);

        $this->service->saveProductSubscription($productSubscription2);
    }

    public function testShouldSaveProductSubscriptionByFormDataWithSuccess()
    {
        $formData = [
            "product_id" => "23",
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


        $result = $this->service->saveFormProductSubscription($formData);
        $this->assertEquals($formData["product_id"], $result->getProductId());
    }

    public function testShouldEditAProductSubscriptionWithSuccess()
    {
        $product = $this->insertProductSubscription();

        $productResult = $this->service->findById($product->getId());
        $this->assertTrue($productResult->getBoleto());
        $this->assertEquals($product, $productResult);

        $product->setBoleto(false);

        $result = $this->service->saveProductSubscription($product);
        $this->assertEquals($product->getProductId(), $result->getProductId());
        $this->assertFalse($result->getBoleto());
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

        $repo = new ProductSubscriptionRepository();
        $repo->save($productSubscription);

        return $productSubscription;
    }

    public function getRepository()
    {
        return new ProductSubscriptionRepository();
    }
}