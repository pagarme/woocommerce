<?php

namespace Pagarme\Core\Test\Recurrence\Services;

use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;
use Pagarme\Core\Recurrence\Repositories\ProductSubscriptionRepository;
use Pagarme\Core\Recurrence\Services\RecurrenceService;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Core\Test\Abstractions\AbstractSetupTest;

class RecurrenceServiceTest extends AbstractSetupTest
{
    /**
     * @var \Mockery\Mock
     */
    protected $service;

    public function setUp(): void
    {
        $logMock = \Mockery::mock(LogService::class);
        $logMock->shouldReceive('info')->andReturnTrue();

        $this->service = \Mockery::mock(RecurrenceService::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->service->shouldReceive('getLogService')->andReturn($logMock);

        parent::setUp();
    }
    public function testShouldReturnEmptyWhenTheRecurrenceProductNotExists()
    {
        $this->service->shouldReceive('getProductPlan')->andReturnNull();
        $this->assertEmpty($this->service->getRecurrenceProductByProductId(10));
    }

    public function testShouldReturnARecurrenceProductByProductId()
    {
        $recurrenceProduct = $this->insertProductSubscription();

        $this->assertNotEmpty($this->service->getRecurrenceProductByProductId($recurrenceProduct->getProductId()));
    }

    public function testShouldReturnMaxInstallmentByIntervalTypeMonth()
    {
        $interval = IntervalValueObject::month(7);

        $maxInstallment = $this->service->getMaxInstallmentByRecurrenceInterval($interval);

        $this->assertEquals(7, $maxInstallment);
    }

    public function testShouldReturnMaxInstallmentByIntervalTypeYear()
    {
        $interval = IntervalValueObject::year(2);

        $maxInstallment = $this->service->getMaxInstallmentByRecurrenceInterval($interval);

        $this->assertEquals(12, $maxInstallment);
    }

    private function insertProductSubscription()
    {
        $product = [
            "product_id" => "23",
            "boleto" => true,
            "credit_card" => true,
            "allow_installments" => true,
            "sell_as_normal_product" => true,
            "cycles" => 10,
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

        $repo = new ProductSubscriptionRepository();
        $repo->save($productSubscription);

        return $productSubscription;
    }
}