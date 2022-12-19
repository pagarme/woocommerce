<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Pagarme\Core\Payment\ValueObjects\CardToken;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;

class NewDebitCardPayment extends NewCreditCardPayment
{
    /** @var bool */
    private $saveOnSuccess;

    public function __construct()
    {
        $this->saveOnSuccess = false;
        parent::__construct();
    }

    static public function getBaseCode()
    {
        return PaymentMethod::debitCard()->getMethod();
    }

    /**
     * @param int $installments
     */
    public function setInstallments($installments)
    {
        if ($installments < 1) {
            throw new InvalidParamException(
                "Installments should be at least 1",
                $installments
            );
        }

        $this->installments = $installments;
    }

    public function isSaveOnSuccess()
    {
        $order = $this->getOrder();
        if ($order === null) {
            return false;
        }

        if (!MPSetup::getModuleConfiguration()->getDebitConfig()->isSaveCards()) {
            return false;
        }

        $customer = $this->getCustomer();

        if ($customer === null) {
            return false;
        }

        return $this->saveOnSuccess;
    }
}