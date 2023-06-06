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

        $this->blurOrderInfo($orderInfo);

        return $orderInfo;
    }

    private function blurOrderInfo($orderInfo)
    {
        $charges = $orderInfo->core->data->getCharges();
        foreach ($charges as $charge) {
            $transactions = $charge->getTransactions();

            foreach ($transactions as $transaction) {
                if ( !empty( $transaction->getCardData() ) ) {
                    $ownerName = $transaction->getCardData()
                        ->getOwnerName();
                    $transaction->getCardData()
                        ->setOwnerName(preg_replace('/(?<=\S{2})\S/', '*', $ownerName ?? ""));
                }

                $transaction->getPostData()
                    ->card_data = null;

                $transaction->getPostData()
                    ->tran_data = null;
            }
        }
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

        $this->blurPlatformOrderInfo($platformOrderInfo);

        return $platformOrderInfo;
    }

    private function blurPlatformOrderInfo($platformOrderInfo)
    {
        $regex = '/(?<=\S{2})\S/';

        $platformOrderInfo->order['customer_email'] = preg_replace('/^.{3}\K|.(?=.*@)/','*', $platformOrderInfo->order['customer_email'] ?? "");
        $platformOrderInfo->order['customer_firstname'] = preg_replace($regex, '*', $platformOrderInfo->order['customer_firstname'] ?? "");
        $platformOrderInfo->order['customer_lastname'] = preg_replace($regex, '*', $platformOrderInfo->order['customer_lastname'] ?? "");
        $platformOrderInfo->order['customer_middlename'] = preg_replace($regex, '*', $platformOrderInfo->order['customer_middlename'] ?? "");
        $platformOrderInfo->order['customer_taxvat'] = preg_replace($regex, '*', $platformOrderInfo->order['customer_taxvat'] ?? "");
        $platformOrderInfo->payments[0]['cc_owner'] = preg_replace($regex, '*', $platformOrderInfo->payments[0]['cc_owner'] ?? "");
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