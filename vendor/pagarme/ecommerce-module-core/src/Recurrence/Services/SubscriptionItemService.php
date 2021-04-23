<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Kernel\Interfaces\PlatformProductInterface;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;

class SubscriptionItemService
{
    public function updateStock($items)
    {
        if (empty($items)) {
            return;
        }

        foreach ($items as $item) {
            $product = $this->getProductDecorated($item->getCode());
            $product->decreaseStock($item->getQuantity());
        }
    }

    public function getProductDecorated($code)
    {
        $productDecorator =
            AbstractModuleCoreSetup::get(
                AbstractModuleCoreSetup::CONCRETE_PRODUCT_DECORATOR_CLASS
            );

        /**
         * @var PlatformProductInterface $product
         */
        $product = new $productDecorator();
        $product->loadByEntityId($code);

        return $product;
    }
}