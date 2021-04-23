<?php

namespace Pagarme\Core\Payment\Traits;

use Pagarme\Core\Payment\Aggregates\Order;

trait WithOrderTrait
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}