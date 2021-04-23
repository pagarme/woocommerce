<?php

namespace Pagarme\Core\Test\Recurrence\Services\CartRules;

use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Interfaces\ProductSubscriptionInterface;
use Pagarme\Core\Recurrence\Interfaces\RepetitionInterface;
use Pagarme\Core\Recurrence\Services\CartRules\CurrentProduct;
use PHPUnit\Framework\TestCase;

class CurrentProductTest extends TestCase
{
    public function testSholdReturnARecurrenceProductOnCurrentProduct()
    {
        $currentProduct = new CurrentProduct();

        $productSubscription = new ProductSubscription();
        $repetitionSelected = new Repetition();

        $currentProduct->setProductSubscriptionSelected($productSubscription);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        $this->assertInstanceOf(RepetitionInterface::class, $currentProduct->getRepetitionSelected());
        $this->assertInstanceOf(ProductSubscriptionInterface::class, $currentProduct->getProductSubscriptionSelected());
        $this->assertFalse($currentProduct->isNormalProduct());
    }

    public function testSholdReturnANormalProductInCurrentProduct()
    {
        $currentProduct = new CurrentProduct();
        $currentProduct->setIsNormalProduct(true);

        $this->assertTrue($currentProduct->isNormalProduct());
    }
}