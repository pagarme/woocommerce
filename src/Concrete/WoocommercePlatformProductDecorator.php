<?php

namespace Woocommerce\Pagarme\Concrete;

use Pagarme\Core\Kernel\Interfaces\PlatformProductInterface;
use WC_Product;

class WoocommercePlatformProductDecorator implements PlatformProductInterface
{
    /**
     * @var WC_Product
     */
    private $platformProduct;

    public function __construct($platformProduct)
    {
        $this->platformProduct = $platformProduct;
    }

    public function getId()
    {
        return $this->platformProduct->get_id();
    }

    public function getName()
    {
        return $this->platformProduct->get_name();
    }

    public function getDescription()
    {
        return $this->platformProduct->get_description();
    }

    public function getType()
    {
        return $this->platformProduct->get_type();
    }

    public function getStatus()
    {
        return $this->platformProduct->get_status();
    }

    public function getImages()
    {
        return $this->platformProduct->get_image();
    }

    public function getPrice()
    {
        return $this->platformProduct->get_price();
    }

    public function loadByEntityId($entityId)
    {
        $product = wc_get_product_object($entityId);
        $this->platformProduct = $product;
    }

    public function decreaseStock($quantity)
    {
        $quantityAndStock = $this->platformProduct->get_stock_quantity();
        $stock = $quantityAndStock['qty'];

        $newStockQty = $stock - $quantity;

        if ($newStockQty <= 0) {
            $newStockQty = 0;
        }

        $this->platformProduct->set_stock_quantity($newStockQty);
        $this->platformProduct->save();
    }
}
