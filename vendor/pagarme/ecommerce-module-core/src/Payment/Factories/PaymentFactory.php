<?php

namespace Pagarme\Core\Payment\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Services\InstallmentService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\Authentication;
use Pagarme\Core\Payment\Aggregates\Payments\BoletoPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewDebitCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\NewVoucherPayment;
use Pagarme\Core\Payment\Aggregates\Payments\PixPayment;
use Pagarme\Core\Payment\Aggregates\Payments\GooglePayPayment;
use Pagarme\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\SavedVoucherCardPayment;
use Pagarme\Core\Payment\ValueObjects\BoletoBank;
use Pagarme\Core\Payment\ValueObjects\CardId;
use Pagarme\Core\Payment\ValueObjects\CardToken;
use Pagarme\Core\Payment\ValueObjects\PaymentMethod;
use Pagarme\Core\Payment\Aggregates\Payments\SavedDebitCardPayment;

final class PaymentFactory
{
    /** @var string[] */
    private $primitiveFactories;
    /** @var Configuration  */
    private $moduleConfig;
    /** @var string */
    private $cardStatementDescriptor;

    public function __construct()
    {
        $this->primitiveFactories = [
            'createCreditCardPayments',
            'createBoletoPayments',
            'createVoucherPayments',
            'createDebitCardPayments',
            'createPixPayments',
            'createGooglePayPayments',
        ];

        $this->moduleConfig = MPSetup::getModuleConfiguration();

        $this->cardStatementDescriptor = $this->moduleConfig->getCardStatementDescriptor();
    }

    public function createFromJson($json)
    {
        $data = json_decode($json);

        $payments = [];

        foreach ($this->primitiveFactories as $creator) {
            $payments = array_merge($payments, $this->$creator($data));
        }

        return $payments;
    }

    private function createCreditCardPayments($data)
    {
        $cardDataIndex = AbstractCreditCardPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $this->moduleConfig
            );
        }

        return $payments;
    }

    private function createDebitCardPayments($data)
    {
        $cardDataIndex = NewDebitCardPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $config = $this->moduleConfig->getDebitConfig();
        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $config
            );
        }

        return $payments;
    }

    private function createBasePayments(
        $cardData,
        $cardDataIndex,
        $config
    ) {
        $payment = $this->createBaseCardPayment($cardData, $cardDataIndex);

        if ($payment === null) {
            return;
        }

        $customer = $this->createCustomer($cardData);
        if ($customer !== null) {
            $payment->setCustomer($customer);
        }

        $brand = $cardData->brand;
        $payment->setBrand(CardBrand::$brand());

        $payment->setAmount($cardData->amount);
        $payment->setInstallments($cardData->installments);
        $payment->setRecurrenceCycle($cardData->recurrenceCycle ?? null);
        $payment->setPaymentOrigin($cardData->paymentOrigin ?? null);
        if (!empty($cardData->authentication)) {
            $payment->setAuthentication(Authentication::createFromStdClass($cardData->authentication));
        }

        //setting amount with interest
        if (strcmp($cardDataIndex, \Pagarme\Core\Kernel\ValueObjects\PaymentMethod::VOUCHER)) {
            $payment->setAmount(
                $this->getAmountWithInterestForCreditCard(
                    $payment,
                    $config
                )
            );
        }

        $payment->setCapture($config->isCapture());
        $payment->setStatementDescriptor($config->getCardStatementDescriptor());

        return $payment;
    }

    private function createVoucherPayments($data)
    {
        $cardDataIndex = NewVoucherPayment::getBaseCode();

        if (!isset($data->$cardDataIndex)) {
            return [];
        }

        $config = $this->moduleConfig
            ->getVoucherConfig();

        $cardsData = $data->$cardDataIndex;

        $payments = [];
        foreach ($cardsData as $cardData) {
            $payments[] = $this->createBasePayments(
                $cardData,
                $cardDataIndex,
                $config
            );
        }
        return $payments;
    }

    private function createCustomer($paymentData)
    {
        $multibuyerEnabled = MPSetup::getModuleConfiguration()->isMultiBuyer();
        if (empty($paymentData->customer) || !$multibuyerEnabled) {
            return null;
        }

        $customerFactory = new CustomerFactory();

        return $customerFactory->createFromJson(json_encode($paymentData->customer));
    }

    private function getAmountWithInterestForCreditCard(
        AbstractCreditCardPayment $payment,
                                  $config
    ) {
        $installmentService = new InstallmentService();

        $validInstallments = $installmentService->getInstallmentsFor(
            null,
            $payment->getBrand(),
            $payment->getAmount(),
            $config
        );

        foreach ($validInstallments as $validInstallment) {
            if ($validInstallment->getTimes() === $payment->getInstallments()) {
                return $validInstallment->getTotal();
            }
        }

        throw new \Exception('Invalid installment number!');
    }

    private function createBoletoPayments($data)
    {
        $boletoDataIndex = BoletoPayment::getBaseCode();

        if (!isset($data->$boletoDataIndex)) {
            return [];
        }

        $boletosData = $data->$boletoDataIndex;

        $payments = [];
        foreach ($boletosData as $boletoData) {
            $payment = new BoletoPayment();

            $customer = $this->createCustomer($boletoData);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $payment->setAmount($boletoData->amount);
            if (property_exists($boletoData, 'bank')) {
                $bank = BoletoBank::createFromCode($boletoData->bank);
                if ($bank) {
                    $payment->setBank($bank);
                }
            }
            $payment->setInstructions($boletoData->instructions);
            $payment->setDueAt($boletoData->due_at);

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param array $data
     * @return PixPayment[]
     * @throws InvalidParamException
     */
    private function createPixPayments($data)
    {
        $pixDataIndex = PixPayment::getBaseCode();

        if (!isset($data->$pixDataIndex)) {
            return [];
        }

        $pixData = $data->$pixDataIndex;

        $payments = [];
        foreach ($pixData as $value) {
            $payment = new PixPayment();

            $expiresIn = $this->moduleConfig->getPixConfig()->getExpirationQrCode();
            $payment->setExpiresIn($expiresIn);

            $customer = $this->createCustomer($value);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            $additionalInformation =
                $this->moduleConfig->getPixConfig()->getAdditionalInformation();

            if (!empty($additionalInformation)) {
                $payment->setAdditionalInformation($additionalInformation);
            }

            $payment->setAmount($value->amount);

            $payments[] = $payment;
        }

        return $payments;
    }


    /**
     * @param array $data
     * @return GooglePayPayment[]
     * @throws InvalidParamException
     */
    private function createGooglePayPayments($data)
    {
        $googlepayDataIndex = "googlepay";

        if (!isset($data->$googlepayDataIndex)) {
            return [];
        }

        $googlepayData = $data->$googlepayDataIndex;

        $payments = [];
        foreach ($googlepayData as $value) {
            $payment = new GooglePayPayment();

            $customer = $this->createCustomer($value);
            if ($customer !== null) {
                $payment->setCustomer($customer);
            }

            if (!empty($value->additionalInformation)) {
                $payment->setAdditionalInformation($value->additionalInformation);
            }
            $payment->setBillingAddress($value->billing_address);
            $payment->setAmount($value->amount);

            $payments[] = $payment;
        }

        return $payments;
    }

    /**
     * @param $identifier
     * @return AbstractCreditCardPayment|null
     */
    private function createBaseCardPayment($data, $method)
    {
        $identifier = $data->identifier;
        try {
            $cardToken = new CardToken($identifier);
            $payment = $this->getNewPaymentMethod($method);
            $payment->setIdentifier($cardToken);

            if (isset($data->saveOnSuccess)) {
                $payment->setSaveOnSuccess($data->saveOnSuccess);
            }
            return $payment;
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }

        try {
            $cardId = new CardId($identifier);
            $payment = $this->getSavedPaymentMethod($method);
            $payment->setIdentifier($cardId);

            if (isset($data->cvvCard)) {
                $payment->setCvv($data->cvvCard);
            }

            $owner = new CustomerId($data->customerId);
            $payment->setOwner($owner);

            return $payment;
        } catch (\Exception $e) {
        } catch (\Throwable $e) {
        }

        return null;
    }

    /**
     * @param $method
     * @return SavedCreditCardPayment|SavedVoucherCardPayment|SavedDebitCardPayment
     * @todo Add voucher saved payment
     */
    private function getSavedPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new SavedCreditCardPayment(),
            PaymentMethod::DEBIT_CARD => new SavedDebitCardPayment(),
            PaymentMethod::VOUCHER => new SavedVoucherCardPayment(),
        ];

        if (isset($payments[$method])) {
            return $payments[$method];
        }

        throw new \Exception("payment method saved not found", 400);
    }

    /**
     * @param $method
     * @return NewCreditCardPayment|NewVoucherPayment
     */
    private function getNewPaymentMethod($method)
    {
        $payments = [
            PaymentMethod::CREDIT_CARD => new NewCreditCardPayment(),
            PaymentMethod::VOUCHER => new NewVoucherPayment(),
            PaymentMethod::DEBIT_CARD => new NewDebitCardPayment(),
        ];

        if (!empty($payments[$method])) {
            return $payments[$method];
        }

        return new NewCreditCardPayment();
    }
}
