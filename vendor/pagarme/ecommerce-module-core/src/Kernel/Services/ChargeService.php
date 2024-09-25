<?php

namespace Pagarme\Core\Kernel\Services;

use PagarmeCoreApiLib\Models\GetChargeResponse;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\Repositories\ChargeRepository;
use Pagarme\Core\Kernel\Repositories\OrderRepository;
use Pagarme\Core\Kernel\Responses\ServiceResponse;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\ChargeId;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Services\ResponseHandlers\OrderHandler;
use Pagarme\Core\Webhook\Services\ChargeOrderService;
use Unirest\Exception;

class ChargeService
{
    /** @var LogService  */
    protected $logService;

    public function __construct()
    {
        $this->logService = new LogService(
            'ChargeService',
            true
        );
    }

    public function captureById($chargeId, $amount = 0)
    {
        try {
            $chargeRepository = new ChargeRepository();
            $charge = $chargeRepository->findByPagarmeId(
                new ChargeId($chargeId)
            );

            if ($charge === null) {
                throw new Exception("Charge not found");
            }

            return $this->capture($charge, $amount);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function cancelById($chargeId, $amount = 0)
    {
        try {
            $chargeRepository = new ChargeRepository();
            $charge = $chargeRepository->findByPagarmeId(
                new ChargeId($chargeId)
            );

            if ($charge === null) {
                throw new Exception("Charge not found");
            }

            return $this->cancel($charge, $amount);
        } catch (Exception $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @param Charge $charge
     * @param int $amount
     * @return ServiceResponse
     * @throws InvalidParamException
     */
    public function capture(Charge $charge, $amount = 0)
    {
        $order = (new OrderRepository())->findByPagarmeId(
            new OrderId($charge->getOrderId()->getValue())
        );

        $this->logService->info("Charge capture");
        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $chargeOrderService = new ChargeOrderService();

        $platformOrder = $order->getPlatformOrder();

        $apiService = new APIService();
        $this->logService->info(
            "Capturing charge on Pagarme - {$charge->getPagarmeId()->getValue()}"
        );

        $resultApi = $apiService->captureCharge($charge, $amount);

        if ($resultApi instanceof GetChargeResponse) {
            if (!$charge->getStatus()->equals(ChargeStatus::paid())) {
                $this->logService->info(
                    "Pay charge - {$charge->getPagarmeId()->getValue()}"
                );
                $charge->pay($amount);
            }

            if ($charge->getPaidAmount() == 0) {
                $charge->setPaidAmount($amount);
            }

            $this->logService->info("Update Charge on Order");
            $order->updateCharge($charge);
            $orderRepository->save($order);

            $this->logService->info("Adding history on Order");
            $history = $chargeOrderService->prepareHistoryComment($charge);
            $platformOrder->addHistoryComment($history);

            $this->logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($order, false);

            $this->logService->info("Change Order status");

            $order->applyOrderStatusFromCharges();

            $orderHandlerService = new OrderHandler();

            if (!empty($charge->getCustomer())) {
                $order->setCustomer($charge->getCustomer());
            }

            $orderHandlerService->handle($order);

            $message = $chargeOrderService->prepareReturnMessage($charge);

            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }

    /**
     * @param Charge $charge
     * @return ServiceResponse
     */
    public function cancelJustAtPagarme(Charge $charge)
    {
        $this->logService->info("Call just Charge cancel");

        $this->logService->info(
            "Cancel charge on Pagarme - " . $charge->getPagarmeId()->getValue()
        );

        $apiService = new APIService();
        $resultApi = $apiService->cancelCharge($charge);

        if ($resultApi === null) {
            $i18n = new LocalizationService();

            $message = $i18n->getDashboard("Charge canceled with success");
            return new ServiceResponse(true, $message);
        }

        $this->logService->info("try call just Charge cancel");
        return new ServiceResponse(false, $resultApi);
    }

    public function cancel(Charge $charge, $amount = 0)
    {
        $order = (new OrderRepository)->findByPagarmeId(
            new OrderId($charge->getOrderId()->getValue())
        );

        $this->logService->info("Charge cancel");

        $orderRepository = new OrderRepository();
        $orderService = new OrderService();
        $i18n = new LocalizationService();

        $apiService = new APIService();
        $this->logService->info(
            "Cancel charge on Pagarme - " . $charge->getPagarmeId()->getValue()
        );

        $resultApi = $apiService->cancelCharge($charge, $amount);

        if ($resultApi === null) {

            $this->logService->info("Update Charge on Order");
            $order->updateCharge($charge);
            $orderRepository->save($order);
            $history = $this->prepareHistoryComment($charge);

            $this->logService->info("Adding history on Order");
            $order->getPlatformOrder()->addHistoryComment($history);

            $this->logService->info("Synchronizing with platform Order");
            $orderService->syncPlatformWith($order, false);

            $message = $i18n->getDashboard("Charge canceled with success");
            $this->logService->info($message);
            return new ServiceResponse(true, $message);
        }

        return new ServiceResponse(false, $resultApi);
    }

    /**
     * @param Charge[] $listCharge
     * @return Charge[]
     */
    public function getNotFailedOrCanceledCharges(array $listCharge)
    {
        $existStatusFailed = null;
        $listChargesPaid = [];

        $existStatusFailed = array_filter(
            $listCharge,
            function (Charge $charge) {
                return (
                    ($charge->getStatus()->getStatus() == 'failed'));
            }
        );

        if ($existStatusFailed != null) {
            $listChargesPaid = array_filter(
                $listCharge,
                function (Charge $charge) {
                    return ($charge->getStatus()->getStatus() == 'paid' ||
                        $charge->getStatus()->getStatus() == 'underpaid' ||
                        $charge->getStatus()->getStatus() == 'pending');
                }
            );
        }

        return $listChargesPaid;
    }

    public function prepareHistoryComment(ChargeInterface $charge)
    {
        $i18n = new LocalizationService();
        $moneyService = new MoneyService();

        if (
            $charge->getStatus()->equals(ChargeStatus::paid())
            || $charge->getStatus()->equals(ChargeStatus::overpaid())
            || $charge->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $amountInCurrency = $moneyService->centsToPriceWithCurrencySymbol($charge->getPaidAmount());

            $history = $i18n->getDashboard(
                'Payment received: %s',
                $amountInCurrency
            );

            $extraValue = $charge->getPaidAmount() - $charge->getAmount();
            if ($extraValue > 0) {
                $history .= ". " . $i18n->getDashboard(
                    "Extra amount paid: %s",
                    $moneyService->centsToPriceWithCurrencySymbol($extraValue)
                );
            }

            if ($extraValue < 0) {
                $history .= ". " . $i18n->getDashboard(
                    "Remaining amount: %s",
                    $moneyService->centsToPriceWithCurrencySymbol(abs($extraValue))
                );
            }

            $refundedAmount = $charge->getRefundedAmount();
            if ($refundedAmount > 0) {
                $history = $i18n->getDashboard(
                    'Refunded amount: %s',
                    $moneyService->centsToPriceWithCurrencySymbol($refundedAmount)
                );
                $history .= " (" . $i18n->getDashboard('until now') . ")";
            }

            $canceledAmount = $charge->getCanceledAmount();
            if ($canceledAmount > 0) {
                $amountCanceledInCurrency = $moneyService->centsToPriceWithCurrencySymbol($canceledAmount);

                $history .= " ({$i18n->getDashboard('Partial Payment')}";
                $history .= ". " .
                    $i18n->getDashboard(
                        'Canceled amount: %s',
                        $amountCanceledInCurrency
                    ) . ')';
            }

            return $history;
        }

        $amountInCurrency = $moneyService->centsToPriceWithCurrencySymbol($charge->getRefundedAmount());
        $history = $i18n->getDashboard(
            'Charge canceled.'
        );

        $history .= ' ' . $i18n->getDashboard(
            'Refunded amount: %s',
            $amountInCurrency
        );

        $history .= " (" . $i18n->getDashboard('until now') . ")";

        return $history;
    }

    /**
     * @param $code
     * @return array|null
     * @throws Exception
     */
    public function findChargeWithOutOrder($code)
    {
        $chargeRepository = new ChargeRepository();

        return $chargeRepository->findChargeWithOutOrder($code);
    }

    /**
     * @param $code
     *
     * @return Charge[]
     * @throws \Exception
     */
    public function findChargesByCode($code)
    {
        $chargeRepository = new ChargeRepository();

        return $chargeRepository->findChargesByCode($code);
    }
    
    /**
     * @param Charge $charge
     * @throws Exception
     */
    public function save(Charge $charge)
    {
        $chargeRepository = new ChargeRepository();

        try {
            $chargeRepository->save($charge);
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }
}
