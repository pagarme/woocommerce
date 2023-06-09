<?php

namespace Pagarme\Core\Recurrence\Services;

use Pagarme\Core\Kernel\Services\APIService;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Payment\Services\ResponseHandlers\ErrorExceptionHandler;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use Pagarme\Core\Recurrence\Factories\ChargeFactory;
use Pagarme\Core\Recurrence\Factories\InvoiceFactory;
use Pagarme\Core\Recurrence\Repositories\ChargeRepository;
use Pagarme\Core\Recurrence\ValueObjects\InvoiceStatus;

class InvoiceService
{
    private $logService;
    /**
     * @var LocalizationService
     */
    private $i18n;
    private $apiService;

    public function __construct()
    {

    }

    public function getById($invoiceId)
    {

    }

    public function cancel($invoiceId)
    {
        try {
            $logService = $this->getLogService();
            $charge = $this->getChargeRepository()
                ->findByInvoiceId($invoiceId);

            if (!$charge) {
                $message = 'Invoice not found';

                $logService->info(
                    null,
                    $message . " ID {$invoiceId} ."
                );

                //Code 404
                throw new \Exception($message, 404);
            }

            if ($charge->getStatus()->getStatus() == InvoiceStatus::canceled()->getStatus()) {
                $message = 'Invoice already canceled';

                return [
                    "message" => $message,
                    "code" => 200
                ];
            }
            $invoiceFactory = new InvoiceFactory();
            $invoice = $invoiceFactory->createFromCharge($charge);

            $result = $this->cancelInvoiceAtPagarme($invoice);

            $return = [
                "message" => 'Invoice canceled successfully',
                "code" => 200
            ];

            $logService->info(
                null,
                'Invoice cancel response: ' . $return['message']
            );

            $chargeResult = $result->charge;

            $charge->setStatus(ChargeStatus::canceled());

            if (isset($chargeResult->canceledAmount)) {
                $charge->setCanceledAmount($chargeResult->canceledAmount);
            }

            if (isset($chargeResult->paidAmount)) {
                $charge->setPaidAmount($chargeResult->paidAmount);
            }

            /**
             * @todo Add canceled_at to charge
             */

            $this->getChargeRepository()->save($charge);

            return $return;

        } catch (\Exception $exception) {
            $logService = $this->getLogService();

            $logService->info(
                null,
                $exception->getMessage()
            );

            throw $exception;
        }
    }

    public function cancelInvoiceAtPagarme(Invoice $invoice)
    {
        $logService = $this->getLogService();
        $apiService = $this->getApiService();

        $logService->info(
            null,
            'Invoice cancel request | invoice id: ' .
            $invoice->getPagarmeId()->getValue()
        );

        return $apiService->cancelInvoice($invoice);
    }

    public function setChargedbackStatus(Charge $charge)
    {
        $charge->setMetadata(json_decode($charge->getMetadata()));
        $this->getChargeRepository()->save($charge);
    }

    public function getApiService()
    {
        return new APIService();
    }

    public function getLogService()
    {
        return new LogService('InvoiceService', true);
    }

    public function getChargeRepository()
    {
        return new ChargeRepository();
    }
}