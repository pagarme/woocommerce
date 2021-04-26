<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Factories\ProductSubscriptionFactory;
use PHPUnit\Framework\TestCase;

class ProductSubscriptionFactoryTest extends TestCase
{
    public function testCreateFromPostDataShouldReturnAProductSubscription()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'id' => 456654,
            'product_id' => 12345,
            'boleto' => true,
            'credit_card' => true,
            'allow_installments' => true,
            'sell_as_normal_product' => true,
            'repetitions' => [
                [
                    'interval' => 'month',
                    'interval_count' => 5,
                    'recurrence_price' => 50000,
                    'cycles' => 5,
                ],
                [
                'interval' => 'month',
                'interval_count' => 10,
                'recurrence_price' => 40000,
                ]
            ],
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertEquals($data['id'], $productSubscription->getId());
        $this->assertEquals($data['product_id'], $productSubscription->getProductId());
        $this->assertTrue($productSubscription->getCreditCard());
        $this->assertTrue($productSubscription->getBoleto());
        $this->assertTrue($productSubscription->getAllowInstallments());
        $this->assertCount(2, $productSubscription->getRepetitions());
        $this->assertTrue($productSubscription->getSellAsNormalProduct());
        $this->assertEquals($data['created_at'], $productSubscription->getCreatedAt());
        $this->assertEquals($data['updated_at'], $productSubscription->getUpdatedAt());
    }

    public function testCreateFromPostDataShouldReturnAProductSubscriptionWithoutRepetitions()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'id' => 456654,
            'product_id' => 12345,
            'boleto' => true,
            'credit_card' => true,
            'allow_installments' => true,
            'sell_as_normal_product' => true,
            'repetitions' => [
                [
                    'recurrence_price' => 50000,
                    'cycles' => 5,
                ]
            ],
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertCount(0, $productSubscription->getRepetitions());
    }

    public function testCreateFromPostDataShouldReturnAnEmptyProductSubscription()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $productSubscription = $productSubscriptionFactory->createFromPostData([]);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertEmpty($productSubscription->getId());
        $this->assertEmpty($productSubscription->getProductId());
        $this->assertFalse($productSubscription->getCreditCard());
        $this->assertFalse($productSubscription->getBoleto());
        $this->assertFalse($productSubscription->getAllowInstallments());
        $this->assertCount(0, $productSubscription->getRepetitions());
        $this->assertFalse($productSubscription->getSellAsNormalProduct());
        $this->assertEmpty($productSubscription->getCreatedAt());
        $this->assertEmpty($productSubscription->getUpdatedAt());
    }

    public function testShouldNotReturnAProductSubscriptionObjectIfDontPassAnArrayToFactory()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $productSubscription = $productSubscriptionFactory->createFromPostData("");
        $productSubscription2 = $productSubscriptionFactory->createFromDbData("");
        $this->assertNotInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertNotInstanceOf(ProductSubscription::class, $productSubscription2);
    }

    public function testCreateFromDbShouldReturnAProductSubscription()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $dbData = [
            'id' => 456654,
            'product_id' => 12345,
            'boleto' => "1",
            'credit_card' => "1",
            'allow_installments' => "1",
            'sell_as_normal_product' => "1",
            'billing_type' => "PREPAID",
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $productSubscription = $productSubscriptionFactory->createFromDbData($dbData);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertEquals($dbData['id'], $productSubscription->getId());
        $this->assertEquals($dbData['product_id'], $productSubscription->getProductId());
        $this->assertEquals('PREPAID', $productSubscription->getBillingType());
        $this->assertTrue($productSubscription->getCreditCard());
        $this->assertTrue($productSubscription->getBoleto());
        $this->assertTrue($productSubscription->getAllowInstallments());
        $this->assertTrue($productSubscription->getSellAsNormalProduct());
        $this->assertEquals($dbData['created_at'], $productSubscription->getCreatedAt());
        $this->assertEquals($dbData['updated_at'], $productSubscription->getUpdatedAt());
    }

    public function testShouldNotSetCreditCardValueIfWasNotPassedABooleanValue()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'credit_card' => "true",
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertFalse(
            $productSubscription->getCreditCard(),
            "Should keep the default value (false)"
        );
    }

    public function testShouldNotSetBoletoValueIfWasNotPassedABooleanValue()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'boleto' => "true",
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertFalse(
            $productSubscription->getBoleto(),
            "Should keep the default value (false)"
        );
    }

    public function testShouldNotSetSellAsNormalProductValueIfWasNotPassedABooleanValue()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'sell_as_normal_product' => "true",
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertFalse(
            $productSubscription->getSellAsNormalProduct(),
            "Should keep the default value (false)"
        );
    }

    public function testShouldNotSetAllowInstallmentsValueIfWasNotPassedABooleanValue()
    {
        $productSubscriptionFactory = new ProductSubscriptionFactory();

        $data = [
            'allow_installments' => "true",
        ];

        $productSubscription = $productSubscriptionFactory->createFromPostData($data);

        $this->assertInstanceOf(ProductSubscription::class, $productSubscription);
        $this->assertFalse(
            $productSubscription->getAllowInstallments(),
            "Should keep the default value (false)"
        );
    }
}
