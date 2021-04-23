<?php

namespace Pagarme\Core\Recurrence\Services\CartRules;

use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Recurrence\Services\RulesCheckoutService;

class CompatibleRecurrenceProducts implements RuleInterface
{
    /**
     * @var RulesCheckoutService
     */
    private $rulesCheckoutService;

    /**
     * @var LocalizationService
     */
    private $i18n;

    private $error;

    CONST DEFAULT_MESSAGE = "This product is not compatible with recurrence product in the cart";

    public function __construct()
    {
        $this->rulesCheckoutService = new RulesCheckoutService();
        $this->i18n = new LocalizationService();
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {

        if (
            !$currentProduct->getProductSubscriptionSelected() ||
            !$productListInCart->getRecurrenceProduct()
        ) {
            return;
        }

        $messageConflictRecurrence = $this->getMessageConflict();

        if (empty($messageConflictRecurrence)) {
            $messageConflictRecurrence = self::DEFAULT_MESSAGE;
        }

        $productAreCompatible = $this->rulesCheckoutService->runRulesCheckoutSubscription(
            $productListInCart->getRecurrenceProduct(),
            $currentProduct->getProductSubscriptionSelected(),
            $productListInCart->getRepetition(),
            $currentProduct->getRepetitionSelected()
        );

        if (!$productAreCompatible) {
            $this->setError($messageConflictRecurrence);
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

    public function getMessageConflict()
    {
        return $this->i18n->getDashboard(
            "You can only add two or more subscriptions to your cart that have the same payment method (credit card or boleto) and same frequency (monthly, annual, etc)"
        );
    }
}