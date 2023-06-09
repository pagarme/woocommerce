<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Pagarme\Core\Recurrence\Aggregates\Charge;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;

class Invoice extends AbstractEntity
{
    private $amount;
    private $status;
    private $paymentMethod;
    private $charge;
    private $installments;
    private $totalDiscount;
    private $totalIncrement;
    private $customer;
    private $cycle;

    /**
     * @var SubscriptionItem[]
     */
    private $items;

    /**
     * @return SubscriptionItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param SubscriptionItem[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    public function addItem(SubscriptionItem $item)
    {
        $this->items[] = $item;
    }

    /**
     * @var SubscriptionId
     */
    private $subscriptionId;

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
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @todo Invoice status should be a value object
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return mixed
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /**
     * @param mixed $charge
     */
    public function setCharge(Charge $charge)
    {
        $this->charge = $charge;
    }

    /**
     * @return mixed
     */
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * @param mixed $installments
     * @todo Set param type
     */
    public function setInstallments($installments)
    {
        $this->installments = $installments;
    }

    /**
     * @return mixed
     */
    public function getTotalDiscount()
    {
        return $this->totalDiscount;
    }

    /**
     * @param mixed $totalDiscount
     * @todo Set param type
     */
    public function setTotalDiscount($totalDiscount)
    {
        $this->totalDiscount = $totalDiscount;
    }

    /**
     * @return mixed
     */
    public function getTotalIncrement()
    {
        return $this->totalIncrement;
    }

    /**
     * @param mixed $totalIncrement
     * @todo Set param type
     */
    public function setTotalIncrement($totalIncrement)
    {
        $this->totalIncrement = $totalIncrement;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     *
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     *
     * @param Cycle $cycle
     * @return Invoice
     */
    public function setCycle(Cycle $cycle)
    {
        $this->cycle = $cycle;
        return $this;
    }

    public function getCycleStart()
    {
        if (!empty($this->getCycle())) {
            return $this->getCycle()->getCycleStart();
        }

        return null;
    }

    public function getCycleEnd()
    {
        if (!empty($this->getCycle())) {
            return $this->getCycle()->getCycleEnd();
        }

        return null;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'subscriptionId' => $this->getSubscriptionId()
        ];
    }
}
