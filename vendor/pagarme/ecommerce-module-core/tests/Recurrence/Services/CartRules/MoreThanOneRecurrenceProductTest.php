<?php

namespace Pagarme\Core\Test\Recurrence\Services\CartRules;

use Pagarme\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Pagarme\Core\Recurrence\Aggregates\ProductSubscription;
use Pagarme\Core\Recurrence\Aggregates\Repetition;
use Pagarme\Core\Recurrence\Services\CartRules\CurrentProduct;
use Pagarme\Core\Recurrence\Services\CartRules\MoreThanOneRecurrenceProduct;
use Pagarme\Core\Recurrence\Services\CartRules\ProductListInCart;
use PHPUnit\Framework\TestCase;

class MoreThanOneRecurrenceProductTest extends TestCase
{
    public function testShouldReturnErrorIfTheConfigNotAllowHaveMoreThanOneRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $currentProduct->getProductSubscriptionSelected()->setProductId(10);
        $productListInCart = $this->getProductListInCart();

        $errorMessage = "You cant add more than one recurrence product on the same shopping cart";
        $recurrenceConfigMock = $this->getRecurrenceConfig(false, $errorMessage);

        $rule = new MoreThanOneRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );
        $this->assertNotEmpty($rule->getError());
        $this->assertEquals($errorMessage, $rule->getError());
    }

    public function testShouldBeAbleToAddTheSameProductMoreThanOneTime()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();

        $recurrenceConfigMock = $this->getRecurrenceConfig(false);

        $rule = new MoreThanOneRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );
        $this->assertEmpty($rule->getError());
    }

    public function testShouldNotReturnErrorBecauseTheConfigAllowHaveMoreThanOneRecurrenceProduct()
    {
        $currentProduct = $this->getCurrentProduct();
        $productListInCart = $this->getProductListInCart();
        $recurrenceConfigMock = $this->getRecurrenceConfig();

        $rule = new MoreThanOneRecurrenceProduct($recurrenceConfigMock);
        $rule->run(
            $currentProduct,
            $productListInCart
        );

        $this->assertEmpty($rule->getError());
    }

    protected function getCurrentProduct()
    {
        $currentProduct = new CurrentProduct();

        $productSubscription = new ProductSubscription();
        $repetitionSelected = new Repetition();

        $repetitionSelected->setId(2);

        $currentProduct->setProductSubscriptionSelected($productSubscription);
        $currentProduct->setRepetitionSelected($repetitionSelected);

        return $currentProduct;
    }

    protected function getProductListInCart()
    {
        $productList = new ProductListInCart();

        $productSubscription = new ProductSubscription();
        $repetition = new Repetition();

        $productList->addRecurrenceProduct($productSubscription);
        $productList->setRecurrenceProduct($productSubscription);
        $productList->setRepetition($repetition);

        return $productList;
    }

    protected function getRecurrenceConfig($allow = true, $error = "")
    {
        $recurrenceConfigMock = $this->getMockBuilder(RecurrenceConfig::class)->getMock();
        $recurrenceConfigMock->method('getConflictMessageRecurrenceProductWithRecurrenceProduct')
            ->willReturn($error);

        if ($allow) {
            $recurrenceConfigMock
                ->method('isPurchaseRecurrenceProductWithRecurrenceProduct')
                ->willReturn(true);

            return $recurrenceConfigMock;
        }
        $recurrenceConfigMock
            ->method('isPurchaseRecurrenceProductWithRecurrenceProduct')
            ->willReturn(false);

        return $recurrenceConfigMock;
    }
}