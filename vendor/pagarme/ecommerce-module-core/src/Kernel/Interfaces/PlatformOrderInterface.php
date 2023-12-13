<?php

namespace Pagarme\Core\Kernel\Interfaces;

use Pagarme\Core\Kernel\ValueObjects\Id\OrderId;
use Pagarme\Core\Kernel\ValueObjects\OrderState;
use Pagarme\Core\Kernel\ValueObjects\OrderStatus;
use Pagarme\Core\Marketplace\Aggregates\Split;
use Pagarme\Core\Payment\Aggregates\Customer;
use Pagarme\Core\Payment\Aggregates\Item;
use Pagarme\Core\Payment\Aggregates\Payments\AbstractPayment;
use Pagarme\Core\Payment\Aggregates\Shipping;
use Pagarme\Core\Kernel\Aggregates\Charge;

interface PlatformOrderInterface
{
    public function save();
    /**
     *
     * @return OrderState
     */
    public function getState();
    public function setState(OrderState $state);
    public function setStatus(OrderStatus $status);
    public function getStatus();
    public function loadByIncrementId($incrementId);
    public function addHistoryComment($message, $notifyCustomer);

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function setAdditionalInformation($name, $value);

    /**
     * @param Charge[] $charges
     * @return array[['key' => value]]
     */
    public function extractAdditionalChargeInformation(array $charges);

    /**
     * @param Charge[] $charges
     * @return mixed
     */
    public function addAdditionalInformation(array $charges);
    public function getHistoryCommentCollection();
    public function setIsCustomerNotified();
    public function canInvoice();
    public function canUnhold();
    public function isPaymentReview();
    public function isCanceled();
    public function setPlatformOrder($platformOrder);
    public function getPlatformOrder();

    /**
     * @return string
     */
    public function getPaymentMethodPlatform();
    public function getIncrementId();
    public function payAmount($amount);
    public function refundAmount($amountToRefund);
    public function cancelAmount($amountToRefund);
    public function getGrandTotal();
    public function getBaseTaxAmount();
    public function getTotalPaid();
    public function getTotalDue();
    public function setTotalPaid($amount);
    public function setBaseTotalPaid($amount);
    public function setTotalDue($amount);
    public function setBaseTotalDue($amount);
    public function setTotalCanceled($amount);
    public function setBaseTotalCanceled($amount);
    public function getTotalRefunded();
    public function setTotalRefunded($amount);
    public function setBaseTotalRefunded($amount);
    public function getCode();
    public function getData();
    /**
     *
     * @return OrderId
     */
    public function getPagarmeId();

    /**
     *
     * @return PlatformInvoiceInterface[]
     */
    public function getInvoiceCollection();
    public function getTransactionCollection();
    public function getPaymentCollection();

    /** @return Customer */
    public function getCustomer();
    /** @return Item[] */
    public function getItemCollection();

    /** @return AbstractPayment[] */
    public function getPaymentMethodCollection();
    /** @return null|Shipping */
    public function getShipping();

    /** @return null|Split */
    public function handleSplitOrder();

    /** @since  1.6.5 */
    public function getTotalCanceled();

    /** @since  1.7.2 */
    public function getTotalPaidFromCharges();

    /** @since 1.11.0 */
    public function getPaymentMethod();

    /**
     * @param string $message
     * @return bool
     */
    public function sendEmail($message);

    /**
     * @param string $orderStatus
     * @return string
     */
    public function getStatusLabel(OrderStatus $orderStatus);
}
