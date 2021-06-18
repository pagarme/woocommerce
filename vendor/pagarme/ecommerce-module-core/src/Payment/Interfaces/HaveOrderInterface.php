<?php

namespace Pagarme\Core\Payment\Interfaces;

use Pagarme\Core\Payment\Aggregates\Order;

interface HaveOrderInterface
{
    /**
     * @return Order
     */
    public function getOrder();

    /**
     * @param Order $order
     * @return mixed
     */
    public function setOrder(Order $order);
}