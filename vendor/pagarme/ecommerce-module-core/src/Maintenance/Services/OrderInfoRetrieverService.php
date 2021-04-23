<?php

namespace Pagarme\Core\Maintenance\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Maintenance\Interfaces\InfoRetrieverServiceInterface;

class OrderInfoRetrieverService implements InfoRetrieverServiceInterface
{
    public function retrieveInfo($value)
    {
        $orderInfo = new \stdClass();

        $orderInfo->core = $this->getCoreOrderInfo($value);
        $orderInfo->platform = $this->getPlatformOrderInfo($value);

        return $orderInfo;
    }


    private function getPlatformOrderInfo($orderIncrementId)
    {
        $platformOrderClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);
        /**
         *
 * @var PlatformOrderInterface $platformOrder 
*/
        $platformOrder = new $platformOrderClass();
        $platformOrder->loadByIncrementId($orderIncrementId);

        if ($platformOrder->getCode() === null) {
            return null;
        }

        $platformOrderInfo = new \stdClass();

        $platformOrderInfo->order = $platformOrder->getData();

        $platformOrderInfo->history = $platformOrder->getHistoryCommentCollection();
        $platformOrderInfo->transaction = $platformOrder->getTransactionCollection();
        $platformOrderInfo->payments = $platformOrder->getPaymentCollection();
        $platformOrderInfo->invoices = $platformOrder->getInvoiceCollection();

        return $platformOrderInfo;
    }

    private function getCoreOrderInfo($orderIncrementId)
    {
        $platformOrderClass = MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);
        /**
         *
 * @var PlatformOrderInterface $platformOrder 
*/
        $platformOrder = new $platformOrderClass();
        $platformOrder->loadByIncrementId($orderIncrementId);

        if ($platformOrder->getCode() === null) {
            return null;
        }

        $pagarmeOrderId = $platformOrder->getPagarmeId();

        if ($pagarmeOrderId === null) {
            return null;
        }
        
        $orderRepository = new OrderRepository();

        $data = null;
        try {
            $data = $orderRepository->findByPagarmeId($pagarmeOrderId);
        }catch (\Throwable $e)
        {
        }

        $coreOrder = new \stdClass();
        $coreOrder->mpOrderId = $pagarmeOrderId;
        $coreOrder->data = $data;

        return $coreOrder;
    }
}