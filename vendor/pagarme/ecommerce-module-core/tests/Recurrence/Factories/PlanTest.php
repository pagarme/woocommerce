<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\ValueObjects\PlanId;
use PHPUnit\Framework\TestCase;
use Pagarme\Core\Recurrence\Factories\PlanFactory;
use Pagarme\Core\Recurrence\Aggregates\Plan;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Zend\Db\Sql\Ddl\Column\Datetime;

class PlanFactoryTest extends TestCase
{
    public function testCreateFromPostDataShouldReturnAPlan()
    {
        $planFactory = new PlanFactory();

        $data = [
            'id' => 456654,
            'plan_id' => new PlanId('plan_45asDadb8Xd95451'),
            'name' => "Product Name",
            'description' => "Product Description",
            'billing_type' => 'PREPAID',
            'credit_card' => false,
            'boleto' => true,
            'installments' => false,
            'product_id' => '8081',
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
            'status' => 'ACTIVE',
            'interval_type' => 'month',
            'interval_count' => 5,
            'items' => [
                [
                    'id' => 1,
                    'productId' => 10,
                ]
            ],
            'trial_period_days' => 10
        ];

        $result = $planFactory->createFromPostData($data);

        $this->assertInstanceOf(Plan::class, $result);
    }

    public function testCreateFromPostDataShouldReturnAnEmptyProductSubscription()
    {
        $planFactory = new PlanFactory();

        $productSubscription = $planFactory->createFromPostData([]);

        $this->assertInstanceOf(Plan::class, $productSubscription);

        $this->assertInstanceOf(Plan::class, $productSubscription);
        $this->assertEmpty($productSubscription->getId());
        $this->assertEmpty($productSubscription->getName());
        $this->assertEmpty($productSubscription->getDescription());
        $this->assertEmpty($productSubscription->getInterval());
        $this->assertEmpty($productSubscription->getPagarmeId());
        $this->assertEmpty($productSubscription->getProductId());
        $this->assertEmpty($productSubscription->getCreditCard());
        $this->assertEmpty($productSubscription->getBoleto());
        $this->assertEmpty($productSubscription->getStatus());
        $this->assertEquals("PREPAID", $productSubscription->getBillingType());
        $this->assertEmpty($productSubscription->getAllowInstallments());
        $this->assertEmpty($productSubscription->getCreatedAt());
        $this->assertEmpty($productSubscription->getUpdatedAt());
    }

    public function testShouldNotReturnAPlanObjectIfDontPassAnArrayToFactory()
    {
        $planFactory = new PlanFactory();

        $plan = $planFactory->createFromPostData("");
        $plan2 = $planFactory->createFromDbData("");
        $this->assertNotInstanceOf(Plan::class, $plan);
        $this->assertNotInstanceOf(Plan::class, $plan2);
    }

    public function testCreateFromDbShouldReturnAPlan()
    {
        $planFactory = new PlanFactory();

        $data = [
            'id' => 456654,
            'plan_id' => 'plan_45asDadb8Xd95451',
            'name' => "Product Name",
            'description' => "Product Description",
            'billing_type' => 'PREPAID',
            'credit_card' => false,
            'boleto' => true,
            'installments' => false,
            'product_id' => '8081',
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
            'status' => 'ACTIVE',
            'interval_type' => 'month',
            'interval_count' => 5,
            'items' => [
                [
                    'id' => 1,
                    'productId' => 10,
                ]
            ],
            'trial_period_days' => 10
        ];

        $plan = $planFactory->createFromDbData($data);
        $this->assertInstanceOf(Plan::class, $plan);
    }
}