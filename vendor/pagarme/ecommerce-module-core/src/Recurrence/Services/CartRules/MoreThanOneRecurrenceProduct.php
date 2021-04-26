<?php

namespace Pagarme\Core\Recurrence\Services\CartRules;

use Pagarme\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class MoreThanOneRecurrenceProduct implements RuleInterface
{
    /**
     * @var RecurrenceConfig
     */
    protected $recurrenceConfig;
    private $error;

    CONST DEFAULT_MESSAGE = "It's not possible to add ".
    "recurrence product with another product recurrence";

    public function __construct(RecurrenceConfig $recurrenceConfig)
    {
        $this->recurrenceConfig = $recurrenceConfig;
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddRecurrenceProductWithRecurrenceProduct =
            $this->recurrenceConfig
                ->isPurchaseRecurrenceProductWithRecurrenceProduct();

        $messageConflictRecurrence =
            $this->recurrenceConfig
                ->getConflictMessageRecurrenceProductWithRecurrenceProduct();

        if (empty($messageConflictRecurrence)) {
            $messageConflictRecurrence = self::DEFAULT_MESSAGE;
        }

        $sameRecurrenceProduct = $this->checkIsSameRecurrenceProduct(
            $currentProduct,
            $productListInCart
        );

        if (
            !$canAddRecurrenceProductWithRecurrenceProduct &&
            (
                !$currentProduct->isNormalProduct() &&
                !empty($productListInCart->getRecurrenceProducts())
            ) &&
            !$sameRecurrenceProduct
        ) {
            $this->setError($messageConflictRecurrence);
        }

        return;
    }

    /**
     * @param CurrentProduct $currentProduct
     * @param ProductListInCart $productListInCart
     * @return bool
     */
    private function checkIsSameRecurrenceProduct(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        foreach ($productListInCart->getRecurrenceProducts() as $product) {
            if ($currentProduct->isNormalProduct()) {
                return false;
            }

            $productSubscriptionSelected =
                $currentProduct->getProductSubscriptionSelected();

            if ($product->getProductId() == $productSubscriptionSelected->getProductId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    protected function setError($error)
    {
        $this->error = $error;
    }
}
