<?php

namespace Pagarme\Core\Kernel\Abstractions;

use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\Services\OrderLogService;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Exception;

abstract class AbstractPlatformOrderDecorator implements PlatformOrderInterface
{
    protected $platformOrder;
    private $logService;
    private $paymentMethod;
    private $attempts = 1;

    public function __construct()
    {
        $this->logService = new OrderLogService();
    }
    
    public function getAttempts()
    {
        return $this->attempts;
    }

    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
    }
    public function addHistoryComment($message, $notifyCustomer = false)
    {
        $message = 'PGM - ' . $message;
        $this->addMPHistoryComment($message, $notifyCustomer);
    }

    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    public function setPlatformOrder($platformOrder)
    {
        $this->platformOrder = $platformOrder;
        $this->handleSplitOrder();
    }

    public function setStatus(OrderStatus $status)
    {
        $currentStatus = '';
        try {
            $currentStatus = $this->getStatus();
        } catch (\Throwable $e) {
        }

        $statusInfo = (object)[
            "from" => $currentStatus,
            "to" => $status,

        ];
        $this->logService->orderInfo(
            $this->getCode(),
            'Status Change',
            $statusInfo
        );

        $this->setStatusAfterLog($status);
    }

    public function setState(OrderState $state)
    {
        $currentState = '';
        try {
            $currentState = $this->getState();
        } catch (\Throwable $e) {
        }

        $stateInfo = (object)[
            "from" => $currentState,
            "to" => $state,

        ];
        $this->logService->orderInfo(
            $this->getCode(),
            'State Change',
            $stateInfo
        );

        $this->setStateAfterLog($state);
    }

    public function payAmount($amount)
    {
        $platformOrder = $this->getPlatformOrder();

        /*
         * @todo this format operations should be made by a currency format service.
         *      But before doing this, check if a decorator can depend on a service.
         */

        $amountInCurrency = number_format($amount / 100, 2);
        $grandTotal = number_format($platformOrder->getGrandTotal(), 2);
        $totalPaid = number_format($platformOrder->getTotalPaid(), 2);
        $totalDue = number_format($platformOrder->getTotalDue(), 2);

        $totalPaid += $amountInCurrency;
        if ($totalPaid > $grandTotal) {
            $totalPaid = $grandTotal;
        }

        $totalDue -= $amountInCurrency;
        if ($totalDue < 0) {
            $totalDue = 0;
        }

        $platformOrder->setTotalPaid($totalPaid);
        $platformOrder->setBaseTotalPaid($totalPaid);
        $platformOrder->setTotalDue($totalDue);
        $platformOrder->setBaseTotalDue($totalDue);

        return $amountInCurrency;
    }

    public function cancelAmount($amount)
    {
        $platformOrder = $this->getPlatformOrder();

        /*
         * @todo this format operations should be made by a currency format service.
         *      But before doing this, check if a decorator can depend on a service.
         */

        $amountInCurrency = number_format($amount / 100, 2);
        $grandTotal = number_format($platformOrder->getGrandTotal(), 2);
        $totalCanceled = number_format($platformOrder->getTotalCanceled(), 2);

        $totalCanceled += $amountInCurrency;
        if ($totalCanceled > $grandTotal) {
            $totalCanceled = $grandTotal;
        }

        $platformOrder->setTotalCanceled($totalCanceled);
        $platformOrder->setBaseTotalCanceled($totalCanceled);

        return $amountInCurrency;
    }

    public function refundAmount($amount)
    {
        $platformOrder = $this->getPlatformOrder();

        /*
         * @todo this format operations should be made by a currency format service.
         *      But before doing this, check if a decorator can depend on a service.
         */

        $amountInCurrency = number_format($amount / 100, 2);
        $grandTotal = number_format($platformOrder->getGrandTotal(), 2);
        $totalRefunded = number_format($platformOrder->getTotalRefunded(), 2);

        $totalRefunded += $amountInCurrency;
        if ($totalRefunded > $grandTotal) {
            $totalRefunded = $grandTotal;
        }

        $platformOrder->setTotalRefunded($totalRefunded);
        $platformOrder->setBaseTotalRefunded($totalRefunded);

        return $amountInCurrency;
    }

    public function getTotalPaidFromCharges()
    {
        $mpOrderId = $this->getPagarmeId();
        $grandTotal = $this->getGrandTotal();
        if ($mpOrderId === null) {
            return $grandTotal;
        }

        $orderRepository = new OrderRepository();
        $mpOrder = $orderRepository->findByPagarmeId($mpOrderId);
        if ($mpOrder === null) {
            return $grandTotal;
        }

        $grandTotal = 0;
        foreach ($mpOrder->getCharges() as $charge) {
            $grandTotal += $charge->getPaidAmount();
        }
        $moneyService = new MoneyService();
        $grandTotal = $moneyService->centsToFloat($grandTotal);

        return $grandTotal;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    abstract protected function addMPHistoryComment($message, $notifyCustomer);
    abstract protected function setStatusAfterLog(OrderStatus $status);
    abstract protected function setStateAfterLog(OrderState $state);
}
