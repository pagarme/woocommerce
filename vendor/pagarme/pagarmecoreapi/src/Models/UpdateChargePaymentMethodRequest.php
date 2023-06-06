<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 *Request for updating the payment method of a charge
 */
class UpdateChargePaymentMethodRequest implements JsonSerializable
{
    /**
     * Indicates if the payment method from the subscription must also be updated
     * @required
     * @maps update_subscription
     * @var bool $updateSubscription public property
     */
    public $updateSubscription;

    /**
     * The new payment method
     * @required
     * @maps payment_method
     * @var string $paymentMethod public property
     */
    public $paymentMethod;

    /**
     * Credit card data
     * @required
     * @maps credit_card
     * @var \PagarmeCoreApiLib\Models\CreateCreditCardPaymentRequest $creditCard public property
     */
    public $creditCard;

    /**
     * Debit card data
     * @required
     * @maps debit_card
     * @var \PagarmeCoreApiLib\Models\CreateDebitCardPaymentRequest $debitCard public property
     */
    public $debitCard;

    /**
     * Boleto data
     * @required
     * @var \PagarmeCoreApiLib\Models\CreateBoletoPaymentRequest $boleto public property
     */
    public $boleto;

    /**
     * Voucher data
     * @required
     * @var \PagarmeCoreApiLib\Models\CreateVoucherPaymentRequest $voucher public property
     */
    public $voucher;

    /**
     * Cash data
     * @required
     * @var \PagarmeCoreApiLib\Models\CreateCashPaymentRequest $cash public property
     */
    public $cash;

    /**
     * Bank Transfer data
     * @required
     * @maps bank_transfer
     * @var \PagarmeCoreApiLib\Models\CreateBankTransferPaymentRequest $bankTransfer public property
     */
    public $bankTransfer;

    /**
     * @todo Write general description for this property
     * @required
     * @maps private_label
     * @var \PagarmeCoreApiLib\Models\CreatePrivateLabelPaymentRequest $privateLabel public property
     */
    public $privateLabel;

    /**
     * Constructor to set initial or default values of member properties
     * @param bool                             $updateSubscription Initialization value for $this->updateSubscription
     * @param string                           $paymentMethod      Initialization value for $this->paymentMethod
     * @param CreateCreditCardPaymentRequest   $creditCard         Initialization value for $this->creditCard
     * @param CreateDebitCardPaymentRequest    $debitCard          Initialization value for $this->debitCard
     * @param CreateBoletoPaymentRequest       $boleto             Initialization value for $this->boleto
     * @param CreateVoucherPaymentRequest      $voucher            Initialization value for $this->voucher
     * @param CreateCashPaymentRequest         $cash               Initialization value for $this->cash
     * @param CreateBankTransferPaymentRequest $bankTransfer       Initialization value for $this->bankTransfer
     * @param CreatePrivateLabelPaymentRequest $privateLabel       Initialization value for $this->privateLabel
     */
    public function __construct()
    {
        if (9 == func_num_args()) {
            $this->updateSubscription = func_get_arg(0);
            $this->paymentMethod      = func_get_arg(1);
            $this->creditCard         = func_get_arg(2);
            $this->debitCard          = func_get_arg(3);
            $this->boleto             = func_get_arg(4);
            $this->voucher            = func_get_arg(5);
            $this->cash               = func_get_arg(6);
            $this->bankTransfer       = func_get_arg(7);
            $this->privateLabel       = func_get_arg(8);
        }
    }


    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['update_subscription'] = $this->updateSubscription;
        $json['payment_method']      = $this->paymentMethod;
        $json['credit_card']         = $this->creditCard;
        $json['debit_card']          = $this->debitCard;
        $json['boleto']              = $this->boleto;
        $json['voucher']             = $this->voucher;
        $json['cash']                = $this->cash;
        $json['bank_transfer']       = $this->bankTransfer;
        $json['private_label']       = $this->privateLabel;

        return $json;
    }
}
