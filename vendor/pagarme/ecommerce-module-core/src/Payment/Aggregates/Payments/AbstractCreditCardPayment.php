<?php

namespace Pagarme\Core\Payment\Aggregates\Payments;

use Pagarme\Core\Payment\Aggregates\Payments\Authentication\Authentication;
use PagarmeCoreApiLib\Models\CreateCardRequest;
use PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Services\InstallmentService;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Kernel\Services\MoneyService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Payment\ValueObjects\AbstractCardIdentifier;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;
use PagarmeCoreApiLib\Models\CreatePaymentAuthenticationRequest;

abstract class AbstractCreditCardPayment extends AbstractPayment
{
    /** @var CardBrand */
    protected $brand;
    /** @var int */
    protected $installments;
    /** @var string */
    protected $recurrenceCycle;
    /** @var string */
    protected $paymentOrigin;
    /** @var string */
    protected $statementDescriptor;
    /** @var boolean */
    protected $capture;
    /** @var AbstractCardIdentifier */
    protected $identifier;
    /** @var Authentication|null */
    protected $authentication;


    public function __construct()
    {
        $this->installments = 1;
        $this->capture = true;
    }

    /**
     * @return int
     */
    public function getInstallments()
    {
        return $this->installments;
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

        $installmentsEnabled = MPSetup::getModuleConfiguration()
            ->isInstallmentsEnabled();

        if (!$installmentsEnabled && $installments > 1) {
            throw new InvalidParamException(
                "Trying to set installment number greater than 1 when installments is disabled!",
                $installments
            );
        }

        //amount defined?
        if ($this->amount === null) {
            throw new \Exception(
                "Amount must be defined before adding installments",
                400
            );
        }

        //brand added?
        if ($this->brand === null) {
            throw new \Exception(
                "Card brand must be defined before adding installments",
                400
            );
        }

        //check if the installment is applicable to brand, value and (@todo) order;
        $this->validateIfIsRealInstallment($installments);

        $this->installments = $installments;
    }

    /**
     * @return bool
     */
    private function validateIfIsRealInstallment($installments)
    {
        $i18n = new LocalizationService();

        //get valid installments for this brand.
        $installmentService = new InstallmentService();
        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $this->brand,
            $this->amount
        );

        //check each installemnt
        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $installments) {
                return;
            }
        }

        //invalid installment
        $moneyService = new MoneyService();
        $exception =
            "The card brand '%s' or the amount %.2f doesn't allow " .
            "%d installment(s)! Please review the information and try again.";
        $exception = $i18n->getDashboard(
            $exception,
            $this->brand->getName(),
            $moneyService->centsToFloat($this->amount),
            $installments
        );
        throw new InvalidParamException(
            $exception,
            $installments
        );
    }

    public function getRecurrenceCycle()
    {
        return $this->recurrenceCycle;
    }

    public function setRecurrenceCycle($recurrenceCycle)
    {
        $this->recurrenceCycle = $recurrenceCycle;
    }

    public function getPaymentOrigin()
    {
        return $this->paymentOrigin;
    }

    public function setPaymentOrigin($paymentOrigin)
    {
        $this->paymentOrigin = $paymentOrigin;
    }

    /**
     * @return string
     */
    public function getStatementDescriptor()
    {
        return $this->statementDescriptor;
    }

    /**
     * @param string $statementDescriptor
     */
    public function setStatementDescriptor($statementDescriptor)
    {
        $this->statementDescriptor = $statementDescriptor;
    }

    /**
     * @return bool
     */
    public function isCapture()
    {
        return $this->capture;
    }

    /**
     * @param bool $capture
     */
    public function setCapture($capture)
    {
        $this->capture = $capture;
    }

    /**
     * @return AbstractCardIdentifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return CardBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param CardBrand $brand
     */
    public function setBrand(CardBrand $brand)
    {
        //@todo A inactive card brand should be a valid brand for this agg?
        $this->brand = $brand;
    }

    /**
     * @return Authentication|null
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * @param Authentication $authentication
     * @return void
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @return CreatePaymentAuthenticationRequest
     */
    public function getAuthenticationSDK()
    {
        return $this->authentication->convertToSDKRequest();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = parent::jsonSerialize();

        $obj->installments = $this->installments;
        $obj->brand = $this->brand;
        $obj->statementDescriptor = $this->statementDescriptor;
        $obj->capture = $this->capture;
        $obj->identifier = $this->identifier;
        if (!empty($this->authentication)) {
            $obj->authentication = $this->authentication;
        }

        return $obj;
    }

    /**
     * @param AbstractCardIdentifier $identifier
     */
    abstract public function setIdentifier(AbstractCardIdentifier $identifier);

    static public function getBaseCode()
    {
        return PaymentMethod::creditCard()->getMethod();
    }

    /**
     * @return CreateCreditCardPaymentRequest
     */
    protected function convertToPrimitivePaymentRequest()
    {
        $createCardRequest = new CreateCardRequest();
        $createCardRequest->billingAddress = $this->getCustomer()->getAddressToSDK();

        $cardRequest = new CreateCreditCardPaymentRequest();
        $cardRequest->card = $createCardRequest;
        $cardRequest->capture = $this->isCapture();
        $cardRequest->installments = $this->getInstallments();
        $cardRequest->recurrenceCycle = $this->getRecurrenceCycle();
        $cardRequest->paymentOrigin = $this->getPaymentOrigin();
        $cardRequest->statementDescriptor = $this->getStatementDescriptor();
        if (!empty($this->getAuthentication())) {
            $cardRequest->authentication = $this->getAuthenticationSDK();
        }

        return $cardRequest;
    }
}
