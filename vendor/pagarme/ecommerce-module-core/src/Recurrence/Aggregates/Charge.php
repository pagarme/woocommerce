<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\ChargeInterface;
use Pagarme\Core\Kernel\ValueObjects\ChargeStatus;
use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Payment\Traits\WithCustomerTrait;
use Pagarme\Core\Kernel\Aggregates\Transaction;
use Pagarme\Core\Kernel\ValueObjects\PaymentMethod;
use stdClass;

final class Charge extends AbstractEntity implements ChargeInterface
{
    use WithCustomerTrait;

    /**
     *
     * @var OrderId
     */
    private $orderId;
    /**
     *
     * @var int
     */
    private $amount;
    /**
     *
     * @var int
     */
    private $paidAmount;
    /**
     * Holds the amount that will not be captured in any away.
     *
     * @var int
     */
    private $canceledAmount;
    /**
     * Holds the amount that was once captured but then returned to the client.
     *
     * @var int
     */
    private $refundedAmount;

    /**
     *
     * @var string
     */
    private $code;
    /**
     *
     * @var ChargeStatus
     */
    private $status;

    /**
     * @var PaymentMethod
     */
    private $paymentMethod;

    /**
     *
     * @var Transaction[]
     */
    private $transactions;

    private $metadata;

    private $customerId;

    private $invoiceId;

    private $subscriptionId;

    private $boletoLink;/** @fixme */

    private $boletoUrl;

    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var \Datetime
     */
    private $cycleStart;

    /**
     * @var \Datetime
     */
    private $cycleEnd;

    /**
     *
     * @return OrderId
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     *
     * @param OrderId $orderId
     * @return Charge
     */
    public function setOrderId(OrderId $orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     *
     * @param int $amount
     * @throws InvalidParamException
     */
    public function pay($amount)
    {
        $this->setPaidAmount($amount);

        if ($this->amount > $this->getPaidAmount()) {
            $this->status = ChargeStatus::underpaid();
            return;
        }

        $this->status = ChargeStatus::paid();
        $amountToCancel = $this->amount - $this->getPaidAmount();
        $this->setCanceledAmount($amountToCancel);

        if ($this->getLastTransaction()->getPaidAmount() > $this->getAmount()) {
            $this->status = ChargeStatus::overpaid();
            $this->setPaidAmount($this->getLastTransaction()->getPaidAmount());
        }
    }

    /**
     *
     * @param int $amount
     * @throws InvalidParamException
     */
    public function cancel($amount = 0)
    {
        if ($amount === 0) {
            $amount = $this->getPaidAmount();
        }

        if ($this->status->equals(ChargeStatus::paid())) {
            $amountRefunded = $amount + $this->getRefundedAmount();
            $this->setRefundedAmount($amountRefunded);

            //if all the paid amount was canceled, the charge should be canceled.
            if ($amount === $this->paidAmount) {
                $this->status = ChargeStatus::canceled();
            }

            return;
        }

        //if the charge wasn't payed yet the charge should be canceled.
        $this->setCanceledAmount($this->amount);
        $this->status = ChargeStatus::canceled();
    }

    public function chargedback()
    {
        $this->status = ChargeStatus::chargedback();
    }

    public function failed()
    {
        $this->status = ChargeStatus::failed();
    }

    /**
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     *
     * @param int $amount
     * @return Charge
     * @throws InvalidParamException
     */
    public function setAmount($amount)
    {
        if (!is_numeric($amount)) {
            throw new InvalidParamException("Amount should be an integer!", $amount);
        }

        if ($amount < 0) {
            throw new InvalidParamException("Amount should be greater or equal to 0!", $amount);
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getPaidAmount()
    {
        if ($this->paidAmount === null) {
            return 0;
        }

        return $this->paidAmount;
    }

    public function setCycleStart(\DateTime $cycleStart)
    {
        $this->cycleStart = $cycleStart;
        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getCycleStart()
    {
        return $this->cycleStart;
    }

    public function setCycleEnd(\DateTime $cycleEnd)
    {
        $this->cycleEnd = $cycleEnd;
        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getCycleEnd()
    {
        return $this->cycleEnd;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * @return PaymentMethod
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     *
     * @param int $paidAmount
     * @return Charge
     * @throws InvalidParamException
     */
    public function setPaidAmount($paidAmount)
    {
        if (!is_numeric($paidAmount)) {
            throw new InvalidParamException("Amount should be an integer!", $paidAmount);
        }

        if ($paidAmount < 0) {
            $paidAmount = 0;
        }
        $this->paidAmount = $paidAmount;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getCanceledAmount()
    {
        if ($this->canceledAmount === null) {
            return 0;
        }

        return $this->canceledAmount;
    }

    /**
     *
     * @param int $canceledAmount
     * @return Charge
     * @throws InvalidParamException
     */
    public function setCanceledAmount($canceledAmount)
    {
        if (!is_numeric($canceledAmount)) {
            throw new InvalidParamException("Amount should be an integer!", $canceledAmount);
        }

        if ($canceledAmount < 0) {
            $canceledAmount = 0;
        }

        if ($canceledAmount > $this->amount) {
            $canceledAmount = $this->amount;
        }

        $this->canceledAmount = $canceledAmount;
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getRefundedAmount()
    {
        if ($this->refundedAmount === null) {
            return 0;
        }

        return $this->refundedAmount;
    }

    /**
     *
     * @param int $refundedAmount
     * @return Charge
     * @throws InvalidParamException
     */
    public function setRefundedAmount($refundedAmount)
    {
        if (!is_numeric($refundedAmount)) {
            throw new InvalidParamException("Amount should be an integer!", $refundedAmount);
        }

        if ($refundedAmount < 0) {
            $refundedAmount = 0;
        }

        if ($refundedAmount > $this->paidAmount) {
            $refundedAmount = $this->paidAmount;
        }

        $this->refundedAmount = $refundedAmount;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     *
     * @param string $code
     * @return Charge
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     *
     * @return ChargeStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param ChargeStatus $status
     * @return Charge
     */
    public function setStatus(ChargeStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *
     * @return null|Transaction
     */
    public function getLastTransaction()
    {
        $transactions = $this->getTransactions();
        if (count($transactions) === 0) {
            return null;
        }

        $newest = $transactions[0];

        foreach ($transactions as $transaction) {
            if (
                $newest->getCreatedAt()->getTimestamp() <
                $transaction->getCreatedAt()->getTimestamp()
            ) {
                $newest = $transaction;
            }
        }

        return $newest;
    }

    /**
     *
     * @param Transaction $newTransaction
     * @return Charge
     */
    public function addTransaction(Transaction $newTransaction)
    {
        $transactions = $this->getTransactions();
        //cant add a transaction that was already added.
        foreach ($transactions as $transaction) {
            if ($transaction->getPagarmeId()->equals($newTransaction->getPagarmeId())) {
                return $this;
            }
        }

        $transactions[] = $newTransaction;
        $this->transactions = $transactions;

        return $this;
    }

    /**
     *
     * @return Transaction[]
     */
    public function getTransactions()
    {
        if (!is_array($this->transactions)) {
            return [];
        }
        return $this->transactions;
    }

    public function updateTransaction(Transaction $updatedTransaction, $overwriteId = false)
    {
        $transactions = $this->getTransactions();
        foreach ($transactions as &$transaction) {
            if ($transaction->getPagarmeId()->equals($updatedTransaction->getPagarmeId())) {
                $originalId = $transaction->getId();
                $transaction = $updatedTransaction;
                if (!$overwriteId) {
                    $transaction->setId($originalId);
                }
                $this->transactions = $transactions;
                return;
            }
        }

        $this->addTransaction($updatedTransaction);
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @param mixed $metadata
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoice
     */
    public function setInvoice(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getCustomerId()
    {
        if (empty($this->getCustomer())) {
            return null;
        }
        return $this->getCustomer()->getPagarmeId();
    }

    /**
     * @return mixed
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    /**
     * @param mixed $subscriptionId
     */
    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
    }
    /**
     * @return mixed
     */
    public function getBoletoLink()
    {
        return $this->boletoUrl;
    }

    /**
     * @param mixed $boletoLink
     */
    public function setBoletoLink($boletoLink)
    {
        $this->boletoUrl = $boletoLink;
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
        $obj = new stdClass();

        $obj->id = $this->getId();
        $obj->pagarmeId = $this->getPagarmeId();
        $obj->orderId = $this->getOrderId();
        $obj->amount = $this->getAmount();
        $obj->paidAmount = $this->getPaidAmount();
        $obj->canceledAmount = $this->getCanceledAmount();
        $obj->refundedAmount = $this->getRefundedAmount();
        $obj->code = $this->getCode();
        $obj->status = $this->getStatus();
        $obj->transactions = $this->getTransactions();
        $obj->metadata = $this->getMetadata();
        $obj->customerId = $this->getCustomerId();
        $obj->boletoLink = $this->getBoletoLink(); /** @var @fixme boletoUrl */
        $obj->boletoUrl = $this->getBoletoUrl();
        $obj->subscriptionId = $this->getSubscriptionId();
        $obj->invoiceId = $this->getInvoiceId();
        return $obj;
    }

    /**
     * @return string
     */
    public function getBoletoUrl()
    {
        return $this->boletoUrl;
    }

    /**
     * @param string $boletoUrl
     */
    public function setBoletoUrl($boletoUrl)
    {
        $this->boletoUrl = $boletoUrl;
    }
}
