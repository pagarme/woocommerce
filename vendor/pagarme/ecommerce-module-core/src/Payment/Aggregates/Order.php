<?php

namespace Pagarme\Core\Payment\Aggregates;

use Pagarme\Core\Payment\Aggregates\Payments\AbstractCreditCardPayment;
use Pagarme\Core\Payment\Aggregates\Payments\Authentication\AuthenticationStatusEnum;
use PagarmeCoreApiLib\Models\CreateOrderRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractPayment;
use Pagarme\Core\Payment\Aggregates\Payments\SavedCreditCardPayment;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;
use Pagarme\Core\Payment\Traits\WithAmountTrait;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod as PaymentMethod;

final class Order extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    use WithAmountTrait;
    use WithCustomerTrait;

    private $paymentMethod;

    /** @var string */
    private $code;
    /** @var Item[] */
    private $items;
    /** @var null|Shipping */
    private $shipping;
    /** @var AbstractPayment[] */
    private $payments;
    /** @var boolean */
    private $closed;
    /** @var int */
    private $attempts = 1;
    /** @var array */
    private $splitData;

    /** @var boolean */
    private $antifraudEnabled;

    public function __construct()
    {
        $this->payments = [];
        $this->items = [];
        $this->closed = true;
    }


    public function getAttempts()
    {
        return $this->attempts;
    }

    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = substr($code, 0, 52);
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item $item
     */
    public function addItem($item)
    {
        $this->items[] = $item;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping|null $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethodName
     */
    public function setPaymentMethod($paymentMethodName)
    {
        $replace = str_replace('_', '', $paymentMethodName ?? '');
        $paymentMethodObject = $replace . 'PaymentMethod';

        $this->paymentMethod = $this->$paymentMethodObject();
    }

    /**
     * @return AbstractPayment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function addPayment(AbstractPayment $payment)
    {
        $this->validatePaymentInvariants($payment);
        $this->addAdditionalSettingsForPaymentInvariants($payment);
        $this->blockOverPaymentAttempt($payment);

        $payment->setOrder($this);

        if ($payment->getCustomer() === null) {
            $payment->setCustomer($this->getCustomer());
        }

        $this->payments[] = $payment;
    }

    /**
     * @return string
     */
    public function generateIdempotencyKey()
    {
        return sha1($this->getCustomer()->getDocument() . $this->getCode() . '-attempt-' . $this->getAttempts());
    }

    /**
     * @return bool
     */
    public function isPaymentSumCorrect()
    {
        if (
            $this->amount === null ||
            empty($this->payments)
        ) {
            return false;
        }

        $sum = 0;
        foreach ($this->payments as $payment) {
            $sum += $payment->getAmount();
        }

        return $this->amount === $sum;
    }

    /**
     *  Blocks any overpayment attempt.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function blockOverPaymentAttempt(AbstractPayment $payment)
    {
        $i18n = new LocalizationService();

        $currentAmount = $payment->getAmount();
        foreach ($this->payments as $currentPayment) {
            $currentAmount += $currentPayment->getAmount();
        }

        /*This block was commented out because this validation is still problematic in the woocommerce module.
        TODO: we will need to make the module work with this code block.
        if ($currentAmount > $this->amount) {
            $message = $i18n->getDashboard(
                "The sum of payments is greater than the order amount! " .
                "Review the information and try again."
            );
            throw new \Exception($message, 400);
        }*/
    }

    /**
     * Calls the invariant validator method of each payment method, if applicable.
     *
     * @param AbstractPayment $payment
     * @throws \Exception
     */
    private function validatePaymentInvariants(AbstractPayment $payment)
    {
        $paymentClass = $this->discoverPaymentMethod($payment);
        $paymentValidator = "validate$paymentClass";

        if (method_exists($this, $paymentValidator)) {
            $this->$paymentValidator($payment);
        }
    }

    private function discoverPaymentMethod(AbstractPayment $payment)
    {
        $paymentClass = get_class($payment);
        $paymentClass = explode('\\', $paymentClass ?? '');
        $paymentClass = end($paymentClass);
        return $paymentClass;
    }

    private function validateSavedCreditCardPayment(SavedCreditCardPayment $payment)
    {
        if ($this->customer === null) {
            throw new \Exception(
                'To use a saved credit card payment in an order ' .
                    'you must add a customer to it.',
                400
            );
        }

        $customerId = $this->customer->getPagarmeId();
        if ($customerId === null) {
            throw new \Exception(
                'You can\'t use a saved credit card of a fresh new customer',
                400
            );
        }

        if (!$customerId->equals($payment->getOwner())) {
            throw new \Exception(
                'The saved credit card informed doesn\'t belong to the informed customer.',
                400
            );
        }
    }

    /**
     * @param AbstractPayment $payment
     * @return void
     */
    private function addAdditionalSettingsForPaymentInvariants(AbstractPayment $payment)
    {
        $parentClass = get_parent_class($payment);

        if ($parentClass === AbstractCreditCardPayment::class) {
            $this->addThreeDSAntiFraudInfo($payment);
        }
    }

    /**
     * @param AbstractCreditCardPayment $payment
     * @return void
     */
    private function addThreeDSAntiFraudInfo(AbstractCreditCardPayment $payment)
    {
        $authentication = $payment->getAuthentication();
        if (empty($authentication)) {
            return;
        }

        $antiFraudEnabled = true;
        if (in_array($authentication->getStatus(), AuthenticationStatusEnum::doesNotNeedToUseAntifraudStatuses())) {
            $antiFraudEnabled = false;
        }
        $this->setAntifraudEnabled($antiFraudEnabled);
    }

    /**
     * @return bool
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * @param bool $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return bool
     */
    public function isAntifraudEnabled()
    {
        $payments = $this->getPayments();

        foreach ($payments as $payment) {
            $payment;
        }

        $antifraudMinAmount = MPSetup::getModuleConfiguration()->getAntifraudMinAmount();

        if ($this->amount < $antifraudMinAmount) {
            return false;
        }
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
    }

    /**

     * @return Split|null
     */
    public function getSplitData()
    {
        return $this->splitData;
    }

    /**
     * @param Split|null $splitData
     */
    public function setSplitData($splitData)
    {
        $this->splitData = $splitData;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
    */
    #[\ReturnTypeWillChange]

    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->customer = $this->getCustomer();
        $obj->code = $this->getCode();
        $obj->items = $this->getItems();

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $obj->shipping = $this->getShipping();
        }

        $obj->payments = $this->getPayments();
        $obj->closed = $this->isClosed();
        $obj->antifraudEnabled = $this->isAntifraudEnabled();

        return $obj;
    }

    /**
     * @return CreateOrderRequest
     */
    public function convertToSDKRequest()
    {
        $orderRequest = new CreateOrderRequest();

        $orderRequest->antifraudEnabled = $this->isAntifraudEnabled();
        $orderRequest->closed = $this->isClosed();
        $orderRequest->code = $this->getCode();
        $orderRequest->customer = $this->getCustomer()->convertToSDKRequest();

        $orderRequest->payments = [];
        foreach ($this->getPayments() as $payment) {
            $orderRequest->payments[] = $payment->convertToSDKRequest();
        }

        if (!empty($this->getSplitData())) {
            $orderRequest = $this->fixRoundedValuesInCharges($orderRequest);
        }

        $orderRequest->items = [];
        foreach ($this->getItems() as $item) {
            $orderRequest->items[] = $item->convertToSDKRequest();
        }

        $shipping = $this->getShipping();
        if ($shipping !== null) {
            $orderRequest->shipping = $shipping->convertToSDKRequest();
        }

        return $orderRequest;
    }

    private function fixRoundedValuesInCharges(&$orderRequest)
    {

        if (count($orderRequest->payments) < 2) {
            return $orderRequest;
        }

        $firstChargeAmount = $orderRequest->payments[0]->amount;
        $firstChargePercentageOfTotal = $firstChargeAmount / $this->getAmount();

        if ($firstChargePercentageOfTotal !== 0.5) {
            return $orderRequest;
        }

        $orderSplitData = $this->getSplitData();

        $wrongValuesPerRecipient = $this->getRecipientWrongValuesMap($orderRequest, $orderSplitData);

        if (!$wrongValuesPerRecipient) {
            return $orderRequest;
        }

        $orderRequest = $this->fixRoundedValues($wrongValuesPerRecipient, $orderRequest);

        return $orderRequest;
    }

    private function getRecipientWrongValuesMap($orderRequest, $splitData)
    {
        $map = [];

        $marketplaceId = $splitData->getMainRecipientOptionConfig();
        $map[$marketplaceId] = $splitData->getMarketplaceComission();

        foreach ($splitData->getSellersData() as $key => $sellerData) {
            $sellerId = $sellerData['pagarmeId'];
            $sellerCommission = $sellerData['commission'];

            $map[$sellerId] = $sellerCommission;
        }


        foreach ($orderRequest->payments as $key => $paymentObject) {
            $paymentSplitDetails = $paymentObject->split;

            foreach ($paymentSplitDetails as $key => $paymentSplitDetailsObject) {
                $amountPerCharge = $paymentSplitDetailsObject->amount;
                $chargeRecipientId = $paymentSplitDetailsObject->recipientId;

                $map[$chargeRecipientId] -= $amountPerCharge;
            }
        }

        foreach ($map as $recipientId => $wrongValue) {
            if ($wrongValue !== 0) {
                return $map;
            }
        }

        return false;
    }

    private function fixRoundedValues($wrongValuesMap, &$orderRequest)
    {

        foreach ($wrongValuesMap as $recipientId => $wrongValue) {
            $payments = $orderRequest->payments;

            foreach ($payments as $key => &$paymentRequest) {
                $paymentRequestAmount = $paymentRequest->amount;
                $splitedAmount = 0;
                $recipientSplitData = null;

                foreach ($paymentRequest->split as $key => &$splitRequest) {
                    $splitedAmount += $splitRequest->amount;

                    if ($splitRequest->recipientId === $recipientId) {
                        $recipientSplitData = $splitRequest;
                    }
                }

                if ($splitedAmount === $paymentRequestAmount) {
                    continue;
                }

                $amountRemovableFromCharge = $splitedAmount - $paymentRequestAmount;

                $recipientSplitData->amount -= $amountRemovableFromCharge;

                $mustRemoveFromOtherCharges = $wrongValue + $amountRemovableFromCharge;

                if (!$mustRemoveFromOtherCharges) {
                    break;
                }
            }
        }
        return $orderRequest;
    }

    private function creditcardPaymentMethod()
    {
        return PaymentMethod::credit_card();
    }

    private function boletoPaymentMethod()
    {
        return PaymentMethod::boleto();
    }

    private function pixPaymentMethod()
    {
        return PaymentMethod::pix();
    }

    private function voucherPaymentMethod()
    {
        return PaymentMethod::voucher();
    }

    private function debitPaymentMethod()
    {
        return PaymentMethod::debit_card();
    }
    private function googlepayPaymentMethod()
    {
        return PaymentMethod::googlepay();
    }
}
