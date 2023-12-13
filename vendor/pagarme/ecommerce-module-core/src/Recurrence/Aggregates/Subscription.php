<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use PagarmeCoreApiLib\Models\CreateCardRequest;
use PagarmeCoreApiLib\Models\CreateSubscriptionRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Payment\Aggregates\Shipping;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;
use Pagarme\Core\Payment\ValueObjects\Discounts;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;
use Pagarme\Core\Recurrence\ValueObjects\PlanId;
use Pagarme\Core\Recurrence\ValueObjects\IntervalValueObject;
use Pagarme\Core\Recurrence\Aggregates\SubProduct;
use Pagarme\Core\Recurrence\Aggregates\Invoice;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;

class Subscription extends AbstractEntity
{
    use WithCustomerTrait;

    const RECURRENCE_TYPE = "subscription";

    /**
     * @var SubscriptionId
     */
    private $subscriptionId;

    /**
     * @var string
     */
    private $code;

    /**
     * @var SubscriptionStatus
     */
    private $status;

    /**
     * @var int
     */
    private $installments;

    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    private $intervalType;

    private $intervalCount;

    private $description;

    /**
     * @var PlanId
     */
    private $planId;

    /**
     * @var Order
     */
    private $platformOrder;
    private $items = [];
    private $billingType;
    private $cardToken;
    private $boletoDays;
    private $cardId;
    private $shipping;
    private $invoice;
    private $statementDescriptor;
    private $createdAt;
    private $updatedAt;

    /**
     * @var Charge[]
     */
    private $charges;

    /**
     * @var Charge
     */
    private $currentCharge;
    private $increment;

    private $currentCycle;

    /**
     * @var Discounts[]
     */
    private $discounts;

    private $recurrenceType = ProductSubscription::RECURRENCE_TYPE;

    /**
     * @var array
     */
    private $metadata = [];

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param array $metadata
     * @return Subscription
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetaData($metadata)
    {
        $newMetaData = array_merge($this->getMetadata(), $metadata);
        $this->setMetadata($newMetaData);
        return $this;
    }

    /**
     * @return Discounts[]
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param Discounts[] $discounts
     */
    public function setDiscounts(array $discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * @param mixed $billingType
     */
    public function setBillingType($billingType)
    {
        $this->billingType = $billingType;
        return $this;
    }

    /**
     * @return SubscriptionId
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param  SubscriptionId $subscriptionId
     * @return $this
     */
    public function setSubscriptionId(SubscriptionId $subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param  string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param SubscriptionStatus $status
     * @return $this
     */
    public function setStatus(SubscriptionStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setInstallments($installments)
    {
        $this->installments = $installments;
        return $this;
    }

    public function getInstallments()
    {
        return $this->installments;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod->getPaymentMethod();
        return $this;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    public function setRecurrenceType($type)
    {
        $this->recurrenceType = $type;
        return $this;
    }

    public function setIntervalType($intervalType)
    {
        $this->intervalType = $intervalType;
        return $this;
    }

    public function getIntervalType()
    {
        return $this->intervalType;
    }

    public function setIntervalCount($intervalCount)
    {
        $this->intervalCount = $intervalCount;
        return $this;
    }

    public function getIntervalCount()
    {
        return $this->intervalCount;
    }

    public function setPlanId(PlanId $planId)
    {
        $this->planId = $planId;
        return $this;
    }

    public function getPlanId()
    {
        return $this->planId;
    }

    /**
     *
     * @return Order
     */
    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    /**
     *
     * @param PlatformOrderInterface $platformOrder
     * @return Subscription
     */
    public function setPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $this->platformOrder = $platformOrder;
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function addItem(SubscriptionItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return mixed
     */
    public function getCardToken()
    {
        return $this->cardToken;
    }

    /**
     * @param mixed $cardToken
     */
    public function setCardToken($cardToken)
    {
        $this->cardToken = $cardToken;
    }

    /**
     * @return mixed
     */
    public function getBoletoDays()
    {
        return $this->boletoDays;
    }

    /**
     * @param mixed $boletoDays
     */
    public function setBoletoDays($boletoDays)
    {
        $this->boletoDays = $boletoDays;
    }

    /**
     * @return mixed
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * @param mixed $cardId
     */
    public function setCardId($cardId)
    {
        $this->cardId = $cardId;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return mixed
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param mixed $invoice
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return Charge[]
     */
    public function getCharges()
    {
        if (!is_array($this->charges)) {
            return [];
        }
        return $this->charges;
    }

    /**
     *
     * @param  ChargeInterface $newCharge
     * @return Subscription
     */
    public function addCharge(ChargeInterface $newCharge)
    {
        $charges = $this->getCharges();
        //cant add a charge that was already added.
        foreach ($charges as $charge) {
            if ($charge->getPagarmeId()->equals(
                $newCharge->getPagarmeId()
            )
            ) {
                return $this;
            }
        }

        $charges[] = $newCharge;
        $this->charges = $charges;

        return $this;
    }

    public function updateCharge(ChargeInterface $updatedCharge, $overwriteId = false)
    {
        $charges = $this->getCharges();

        foreach ($charges as &$charge) {
            if ($charge->getPagarmeId()->equals($updatedCharge->getPagarmeId())) {
                $chargeId = $charge->getId();
                $charge = $updatedCharge;
                if ($overwriteId) {
                    $charge->setId($chargeId);
                }
                $this->charges = $charges;
                return;
            }
        }

        $this->addCharge($updatedCharge);
    }

    public function applyOrderStatusFromCharges()
    {
        if (empty($this->getCharges())) {
            return;
        }

        $listChargeStatus = [];
        foreach ($this->getCharges() as $charge) {
            $listChargeStatus[$charge->getStatus()->getStatus()] =
                $charge->getStatus()->getStatus();
        }

        $chargesStatusEquals = count($listChargeStatus) == 1;

        if (
            $chargesStatusEquals &&
            $this->getCharges()[0]->getStatus()->equals(ChargeStatus::overpaid())
        ) {
            $this->setStatus(SubscriptionStatus::paid());
            return;
        }

        if (
            in_array(ChargeStatus::paid()->getStatus(), $listChargeStatus) &&
            in_array(ChargeStatus::overpaid()->getStatus(), $listChargeStatus)
        ) {
            $this->setStatus(SubscriptionStatus::paid());
        }

        if (
            in_array(ChargeStatus::failed()->getStatus(), $listChargeStatus) &&
            in_array(ChargeStatus::canceled()->getStatus(), $listChargeStatus)
        ) {
            $this->setStatus(SubscriptionStatus::canceled());
        }
        if (
            in_array(ChargeStatus::chargedback()->getStatus(), $listChargeStatus)
        ) {
            $this->setStatus(SubscriptionStatus::canceled());
        }

        if (
            $chargesStatusEquals &&
            !$this->getCharges()[0]->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $currentStatus = reset($listChargeStatus);
            $this->setStatus(SubscriptionStatus::$currentStatus());
        }
    }

    /**
     * @param ChargeInterface[] $charges
     */
    public function setCharges($charges)
    {
        $this->charges = $charges;
    }

    /**
     * @return Increment
     */
    public function getIncrement()
    {
        return $this->increment;
    }

    /**
     * @param Increment $increment
     */
    public function setIncrement(Increment $increment)
    {
        $this->increment = $increment;
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
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return CreateSubscriptionRequest
     */
    public function convertToSdkRequest()
    {
        $subscriptionRequest = new CreateSubscriptionRequest();

        $subscriptionRequest->code = $this->getCode();
        $subscriptionRequest->customer = $this->getCustomer()->convertToSDKRequest();
        $subscriptionRequest->billingType = $this->getBillingType();
        $subscriptionRequest->interval = $this->getIntervalType();
        $subscriptionRequest->intervalCount = $this->getIntervalCount();
        $subscriptionRequest->cardToken = $this->getCardToken();
        $subscriptionRequest->cardId = $this->getCardId();
        $subscriptionRequest->installments = $this->getInstallments();
        $subscriptionRequest->boletoDueDays = $this->getBoletoDays();
        $subscriptionRequest->paymentMethod = $this->getPaymentMethod();
        $subscriptionRequest->description = $this->getDescription();

        if ($this->getShipping() != null) {
            $subscriptionRequest->shipping = $this->getShipping()->convertToSDKRequest();
        }

        $subscriptionRequest->statementDescriptor = $this->getStatementDescriptor();
        $subscriptionRequest->discounts = $this->getDiscounts();
        $subscriptionRequest->planId = $this->getPlanIdValue();

        $subscriptionRequest->items = [];
        foreach ($this->getItems() as $item) {
            $subscriptionRequest->items[] = $item->convertToSDKRequest();
        }

        $this->setCardData($subscriptionRequest);

        $subscriptionRequest->metadata = $this->getMetadata();

        return $subscriptionRequest;
    }

    private function setCardData(CreateSubscriptionRequest $subscriptionRequest)
    {
        if($subscriptionRequest->paymentMethod == PaymentMethod::BOLETO){
            return;
        }

        $card = new CreateCardRequest();
        if ($this->getCustomer()->getAddress() !== null) {
            $card->billingAddress = $this->getCustomer()->getAddress()->convertToSDKRequest();
        }

        $subscriptionRequest->card = $card;
    }

    public function getStatusValue()
    {
        if ($this->getStatus() !== null) {
            return $this->getStatus()->getStatus();
        }
        return null;
    }

    public function getPlanIdValue()
    {
        if ($this->getPlanId() !== null) {
            return $this->getPlanId()->getValue();
        }
        return null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "id" => $this->getId(),
            "subscriptionId" => $this->getPagarmeId(),
            "code" => $this->getCode(),
            "status" => $this->getStatusValue(),
            "paymentMethod" => $this->getPaymentMethod(),
            "planId" => $this->getPlanIdValue(),
            "intervalType" => $this->getIntervalType(),
            "intervalCount" => $this->getIntervalCount(),
            "installments" => $this->getInstallments(),
            "billingType" => $this->getBillingType(),
            "discounts" => $this->getDiscounts()
        ];
    }

    /**
     * @return Cycle
     */
    public function getCurrentCycle()
    {
        return $this->currentCycle;
    }

    /**
     * @param Cycle $currentCycle
     */
    public function setCurrentCycle(Cycle $currentCycle)
    {
        $this->currentCycle = $currentCycle;
    }

    /**
     * @return ChargeInterface
     */
    public function getCurrentCharge()
    {
        return $this->currentCharge;
    }

    /**
     * @param ChargeInterface $currentCharge
     */
    public function setCurrentCharge(ChargeInterface $currentCharge)
    {
        $this->currentCharge = $currentCharge;
    }
}
