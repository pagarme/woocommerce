<?php

namespace Pagarme\Core\Test\Recurrence;

use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\Factories\SubProductFactory;
use Pagarme\Core\Recurrence\ValueObjects\PricingSchemeValueObject;
use PHPUnit\Framework\TestCase;

class SubProductTest extends TestCase
{
    public function testCreateFromPostDataShouldReturnASubProduct()
    {
        $subproductFactory = new SubProductFactory();

        $data = [
            'id' => 456654,
            'product_id' => 12345,
            'product_recurrence_id' => 4567,
            'recurrence_type' => 'plan',
            'name' => "Product Name",
            'description' => "Product Description",
            'quantity' => 5,
            'cycles' => 15,
            'price' => 3000,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $productSubscription = $subproductFactory->createFromPostData($data);

        $this->assertInstanceOf(SubProduct::class, $productSubscription);
        $this->assertEquals($data['id'], $productSubscription->getId());
        $this->assertEquals($data['product_id'], $productSubscription->getProductId());
        $this->assertEquals($data['product_recurrence_id'], $productSubscription->getProductRecurrenceId());
        $this->assertEquals($data['recurrence_type'], $productSubscription->getRecurrenceType());
        $this->assertEquals($data['name'], $productSubscription->getName());
        $this->assertEquals($data['description'], $productSubscription->getDescription());
        $this->assertEquals($data['quantity'], $productSubscription->getQuantity());
        $this->assertEquals($data['cycles'], $productSubscription->getCycles());
        $this->assertInstanceOf(PricingSchemeValueObject::class, $productSubscription->getPricingScheme());
        $this->assertEquals($data['created_at'], $productSubscription->getCreatedAt());
        $this->assertEquals($data['updated_at'], $productSubscription->getUpdatedAt());
    }

    public function testCreateFromPostDataShouldReturnAnEmptySubProduct()
    {
        $subproductFactory = new SubProductFactory();

        $productSubscription = $subproductFactory->createFromPostData([]);

        $this->assertInstanceOf(SubProduct::class, $productSubscription);

        $this->assertEmpty($productSubscription->getId());
        $this->assertEmpty($productSubscription->getProductId());
        $this->assertEmpty($productSubscription->getProductRecurrenceId());
        $this->assertEmpty($productSubscription->getRecurrenceType());
        $this->assertEmpty($productSubscription->getName());
        $this->assertEmpty($productSubscription->getDescription());
        $this->assertEmpty($productSubscription->getCycles());
        $this->assertEmpty($productSubscription->getQuantity());
        $this->assertEmpty($productSubscription->getPricingScheme());
        $this->assertEmpty($productSubscription->getCreatedAt());
        $this->assertEmpty($productSubscription->getUpdatedAt());
    }

    public function testShouldNotReturnASubProductObjectIfDontPassAnArrayToFactory()
    {
        $subproductFactory = new SubProductFactory();

        $productSubscription = $subproductFactory->createFromPostData("");
        $this->assertNotInstanceOf(SubProduct::class, $productSubscription);
    }

    public function testCreateFromDbShouldReturnASubProduct()
    {
        $subproductFactory = new SubProductFactory();

        $dbData = [
            'id' => 456654,
            'product_id' => 12345,
            'product_recurrence_id' => 4567,
            'recurrence_type' => 'plan',
            'name' => "Product Name",
            'description' => "Product Description",
            'quantity' => 5,
            'cycles' => 15,
            'price' => 3000,
            'created_at' => '2019-10-01 10:12:00',
            'updated_at' => '2019-10-01 10:12:00',
        ];

        $productSubscription = $subproductFactory->createFromDbData($dbData);

        $this->assertInstanceOf(SubProduct::class, $productSubscription);
        $this->assertEquals($dbData['id'], $productSubscription->getId());
        $this->assertEquals($dbData['product_id'], $productSubscription->getProductId());
        $this->assertEquals($dbData['product_recurrence_id'], $productSubscription->getProductRecurrenceId());
        $this->assertEquals($dbData['recurrence_type'], $productSubscription->getRecurrenceType());
        $this->assertEquals($dbData['name'], $productSubscription->getName());
        $this->assertEquals($dbData['description'], $productSubscription->getDescription());
        $this->assertEquals($dbData['quantity'], $productSubscription->getQuantity());
        $this->assertEquals($dbData['cycles'], $productSubscription->getCycles());
        $this->assertInstanceOf(PricingSchemeValueObject::class, $productSubscription->getPricingScheme());
        $this->assertEquals($dbData['created_at'], $productSubscription->getCreatedAt());
        $this->assertEquals($dbData['updated_at'], $productSubscription->getUpdatedAt());
    }
}
