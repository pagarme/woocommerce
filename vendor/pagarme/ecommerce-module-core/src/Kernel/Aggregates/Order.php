<?php

namespace Pagarme\Core\Kernel\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\Interfaces\PlatformOrderInterface;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;

final class Order extends AbstractEntity
{
    use WithCustomerTrait;

    /**
     *
     * @var PlatformOrderInterface
     */
    private $platformOrder;

    /**
     *
     * @var OrderStatus
     */
    private $status;
    /**
     *
     * @var Charge[]
     */
    private $charges;

    /**
     *
     * @return PlatformOrderInterface
     */
    public function getPlatformOrder()
    {
        return $this->platformOrder;
    }

    /**
     *
     * @param  PlatformOrderInterface $platformOrder
     * @return Order
     */
    public function setPlatformOrder(PlatformOrderInterface $platformOrder)
    {
        $this->platformOrder = $platformOrder;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCode()
    {
        return $this->platformOrder->getCode();
    }

    /**
     *
     * @return int
     */
    public function getAmount()
    {
        $amount = 0;
        foreach ($this->getCharges() as $charge) {
            $amount += $charge->getAmount();
        }
        return $amount;
    }

    /**
     *
     * @return OrderStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  OrderStatus $status
     * @return Order
     */
    public function setStatus(OrderStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
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
     * @return Transaction|null
     */
    public function getPixOrBilletTransaction()
    {
        foreach ($this->getCharges() as $charge) {
            foreach ($charge->getTransactions() as $transaction) {
                $type = $transaction->getTransactionType()->getType();
                if ($type === 'pix' || $type === 'boleto') {
                    return $transaction;
                }
            }
        }
        return null;
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
            $this->setStatus(OrderStatus::paid());
            return;
        }

        if (
            in_array(ChargeStatus::paid()->getStatus(), $listChargeStatus) &&
            in_array(ChargeStatus::overpaid()->getStatus(), $listChargeStatus)
        ) {
            $this->setStatus(OrderStatus::paid());
        }

        if (
            in_array(ChargeStatus::canceled()->getStatus(), $listChargeStatus)
        ) {
            $this->setStatus(OrderStatus::canceled());
        }

        if (
            in_array(ChargeStatus::failed()->getStatus(), $listChargeStatus)
        )
        {
            $this->setStatus(OrderStatus::failed());
        }

        if (
            $chargesStatusEquals &&
            !$this->getCharges()[0]->getStatus()->equals(ChargeStatus::underpaid())
        ) {
            $currentStatus = reset($listChargeStatus);
            $this->setStatus(OrderStatus::$currentStatus());
        }
    }

    /**
     *
     * @param  ChargeInterface $newCharge
     * @return Order
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

    /**
     * @param ChargeInterface $updatedCharge
     * @param $overwriteId
     *
     * @return void
     */
    public function updateCharge(ChargeInterface $updatedCharge, $overwriteId = false)
    {
        $charges = $this->getCharges();

        foreach ($charges as &$charge) {
            if ($charge->getPagarmeId()->equals($updatedCharge->getPagarmeId())) {
                $chargeId = $charge->getId();
                $charge = $updatedCharge;
                if ($charge->getRefundedAmount() > 0 && $charge->getRefundedAmount() == $charge->getPaidAmount()) {
                    $charge->setStatus(ChargeStatus::canceled());
                }
                if ($overwriteId) {
                    $charge->setId($chargeId);
                }
                $this->charges = $charges;
                return;
            }
        }

        $this->addCharge($updatedCharge);
    }

    public function getSplitInfo()
    {
        $splitInfo = [];
        foreach ($this->getCharges() as $charge) {
            $transaction = $charge->getLastTransaction();
            $postData = $transaction->getPostData();
            $chargeId = $transaction->getChargeId()->getValue();
            if (empty($postData->split)) {
                continue;
            }
            foreach($postData->split as $split) {
                $splitInfo[$chargeId][] = $split->recipient->name .
                    ' (' . $split->recipient->id . ') - ' .
                    $split->type . ': ' . $split->amount;
            }
        }
        return $splitInfo;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->pagarmeId = $this->getPagarmeId();
        $obj->code = $this->getCode();
        $obj->amount = $this->getAmount();
        $obj->status = $this->getStatus();
        $obj->charges = $this->getCharges();
        $obj->customer = $this->getCustomer();

        return $obj;
    }
}
