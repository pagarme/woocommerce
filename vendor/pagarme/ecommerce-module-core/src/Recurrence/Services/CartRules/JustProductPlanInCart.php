<?php

namespace Pagarme\Core\Recurrence\Services\CartRules;

use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Recurrence\Services\CartRules\CurrentProduct;

class JustProductPlanInCart implements RuleInterface
{
    /**
     * @var string
     */
    private $error;

    /**
     * @var LocalizationService
     */
    private $i18n;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    public function run(
        CurrentProduct $currentProduct,
        ProductListInCart $productListInCart
    ) {
        $foundError = false;

        if (!empty($productListInCart->getProductsPlan())) {
            $foundError = true;
        }

        if (
            !empty($productListInCart->getNormalProducts()) &&
            $currentProduct->getProductPlanSelected() !== null
        ) {
            $foundError = true;
        }

        if (
            !empty($productListInCart->getRecurrenceProducts()) &&
            $currentProduct->getProductPlanSelected() !== null
        ) {
            $foundError = true;
        }

        if ($foundError) {
            $this->error = $this->i18n->getDashboard("It's not possible to have" .
                " any other product with a product plan");
        }
    }

    public function getError()
    {
        return $this->error;
    }
}