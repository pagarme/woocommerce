<?php

namespace Pagarme\Core\Test\Recurrence\Services\CartRules;

use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Pagarme\Core\Recurrence\Interfaces\RepetitionInterface;
use Pagarme\Core\Recurrence\Services\CartRules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class ProductListInCartTest extends TestCase
{
    public function testShouldCreateAnProductsListWithNormalProductsAndRecurrenceProducts()
    {
        $productList = new ProductListInCart();

        $normalProduct = new \stdClass();
        $productSubscription = new ProductSubscription();
        $repetition = new Repetition();

        $productList->addRecurrenceProduct($productSubscription);
        $productList->setRecurrenceProduct($productSubscription);
        $productList->setRepetition($repetition);
        $productList->addNormalProducts([$normalProduct]);

        $this->assertCount(1, $productList->getNormalProducts());
        $this->assertCount(1, $productList->getRecurrenceProducts());
        $this->assertInstanceOf(RepetitionInterface::class, $productList->getRepetition());
        $this->assertInstanceOf(ProductSubscriptionInterface::class, $productList->getRecurrenceProduct());
    }
}