<?php

namespace Pagarme\Core\Recurrence\Services\CartRules;

use Pagarme\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;

class NormalWithRecurrenceProduct implements RuleInterface
{
    /**
     * @var RecurrenceConfig
     */
    protected $recurrenceConfig;
    private $error;

    CONST DEFAULT_MESSAGE = "It's not possible to add recurrence product with product normal";

    public function __construct(RecurrenceConfig $recurrenceConfig)
    {
        $this->recurrenceConfig = $recurrenceConfig;
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $canAddNormalProductWithRecurrenceProduct =
            $this->recurrenceConfig
                ->isPurchaseRecurrenceProductWithNormalProduct();

        $messageConflictRecurrence =
            $this->recurrenceConfig
                ->getConflictMessageRecurrenceProductWithNormalProduct();

        if (empty($messageConflictRecurrence)) {
            $messageConflictRecurrence = self::DEFAULT_MESSAGE;
        }

        if (
            !$canAddNormalProductWithRecurrenceProduct &&
            (
                $currentProduct->isNormalProduct() &&
                !empty($productListInCart->getRecurrenceProducts())
            )
        ) {
            $this->setError($messageConflictRecurrence);
            return;
        }

        if (
            !$canAddNormalProductWithRecurrenceProduct  &&
            (
                !$currentProduct->isNormalProduct() &&
                !empty($productListInCart->getNormalProducts())
            )
        ) {
            $this->setError($messageConflictRecurrence);
            return;
        }

        return;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setError($error)
    {
        $this->error = $error;
    }
}
