<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

/**
 *Response object for getting a charge
 */
class GetAccountResponse implements JsonSerializable
{
    /**
     * @todo Write general description for this property
     * @required
     * @var string $id public property
     */
    public $id;

    /**
     * @maps secret_key
     * @var string $secretKey public property
     */
    public $secretKey;

    /**
     * @todo Write general description for this property
     * @required
     * @maps public_key
     * @var string $publicKey public property
     */
    public $publicKey;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $name public property
     */
    public $name;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $timeZone public property
     * @maps time_zone
     */
    public $timeZone;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $defaultCurrency public property
     * @maps default_currency
     */

    public $defaultCurrency;

    /**
     * @todo Write general description for this property
     * @required
     * @var string $status public property
     */
    public $status;

    /**
     * @todo Write general description for this property
     * @var array $domains public property
     */
    public $domains;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $antifraudSettings public property
     * @maps antifraud_settings
     */
    public $antifraudSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $mundipaggSettings public property
     * @maps mundipagg_settings
     */
    public $mundipaggSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $pagarmeSettings public property
     * @maps pagarme_settings
     */
    public $pagarmeSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $creditCardSettings public property
     * @maps credit_card_settings
     */
    public $creditCardSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $debitCardSettings public property
     * @maps debit_card_settings
     */
    public $debitCardSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $voucherSettings public property
     * @maps voucher_settings
     */
    public $voucherSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $boletoSettings public property
     * @maps boleto_settings
     */
    public $boletoSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $bankTransferSettings public property
     * @maps bank_transfer_settings
     */
    public $bankTransferSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $walletSettings public property
     * @maps wallet_settings
     */
    public $walletSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $safetypaySettings public property
     * @maps safetypay_settings
     */
    public $safetypaySettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $facebookSettings public property
     * @maps facebook_settings
     */
    public $facebookSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $generalSettings public property
     * @maps general_settings
     */
    public $generalSettings;

    /**
     * @var array $webhookSettings public property
     * @maps webhook_settings
     */
    public $webhookSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $splitSettings public property
     * @maps split_settings
     */
    public $splitSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $subscriptionSettings public property
     * @maps subscription_settings
     */
    public $subscriptionSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $orderSettings public property
     * @maps order_settings
     */
    public $orderSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $notificationSettings public property
     * @maps notification_settings
     */
    public $notificationSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $cancellationSettings public property
     * @maps guaranteed_cancellation_settings
     */
    public $cancellationSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $renewCardSettings public property
     * @maps renew_card_settings
     */
    public $renewCardSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $cashSettings public property
     * @maps cash_settings
     */
    public $cashSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $checkoutSettings public property
     * @maps checkout_settings
     */
    public $checkoutSettings;

    /**
     * @todo Write general description for this property
     * @required
     * @var array $pixSettings public property
     * @maps pix_settings
     */
    public $pixSettings;

    public function __construct()
    {
        if (30 == func_num_args()) {
            $this->id = func_get_arg(0);
            $this->secretKey = func_get_arg(1);
            $this->publicKey = func_get_arg(2);
            $this->name = func_get_arg(3);
            $this->timeZone = func_get_arg(4);
            $this->defaultCurrency = func_get_arg(5);
            $this->status = func_get_arg(6);
            $this->domains = func_get_arg(7);
            $this->antifraudSettings = func_get_arg(8);
            $this->mundipaggSettings = func_get_arg(9);
            $this->pagarmeSettings = func_get_arg(10);
            $this->creditCardSettings = func_get_arg(11);
            $this->debitCardSettings = func_get_arg(12);
            $this->voucherSettings = func_get_arg(13);
            $this->boletoSettings = func_get_arg(14);
            $this->bankTransferSettings = func_get_arg(15);
            $this->walletSettings = func_get_arg(16);
            $this->safetypaySettings = func_get_arg(17);
            $this->facebookSettings = func_get_arg(18);
            $this->generalSettings = func_get_arg(19);
            $this->webhookSettings = func_get_arg(20);
            $this->splitSettings = func_get_arg(21);
            $this->subscriptionSettings = func_get_arg(22);
            $this->orderSettings = func_get_arg(23);
            $this->notificationSettings = func_get_arg(24);
            $this->cancellationSettings = func_get_arg(25);
            $this->renewCardSettings = func_get_arg(26);
            $this->cashSettings = func_get_arg(27);
            $this->checkoutSettings = func_get_arg(28);
            $this->pixSettings = func_get_arg(29);
        }
    }

    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['id']                               = $this->id;
        $json['secret_key']                       = $this->secretKey;
        $json['public_key']                       = $this->publicKey;
        $json['name']                             = $this->name;
        $json['time_zone']                        = $this->timeZone;
        $json['default_currency']                 = $this->defaultCurrency;
        $json['status']                           = $this->status;
        $json['domains']                          = $this->domains ?? null;
        $json['antifraud_settings']               = $this->antifraudSettings ?? null;
        $json['mundipagg_settings']               = $this->mundipaggSettings ?? null;
        $json['pagarme_settings']                 = $this->pagarmeSettings ?? null;
        $json['credit_card_settings']             = $this->creditCardSettings ?? null;
        $json['debit_card_settings']              = $this->debitCardSettings ?? null;
        $json['voucher_settings']                 = $this->voucherSettings ?? null;
        $json['boleto_settings']                  = $this->boletoSettings ?? null;
        $json['bank_transfer_settings']           = $this->bankTransferSettings ?? null;
        $json['wallet_settings']                  = $this->walletSettings ?? null;
        $json['safetypay_settings']               = $this->safetypaySettings ?? null;
        $json['facebook_settings']                = $this->facebookSettings ?? null;
        $json['general_settings']                 = $this->generalSettings ?? null;
        $json['webhook_settings']                 = $this->webhookSettings ?? null;
        $json['split_settings']                   = $this->splitSettings ?? null;
        $json['subscription_settings']            = $this->subscriptionSettings ?? null;
        $json['order_settings']                   = $this->orderSettings ?? null;
        $json['notification_settings']            = $this->notificationSettings ?? null;
        $json['guaranteed_cancellation_settings'] = $this->cancellationSettings ?? null;
        $json['renew_card_settings']              = $this->renewCardSettings ?? null;
        $json['cash_Settings']                    = $this->cashSettings ?? null;
        $json['checkout_settings']                = $this->checkoutSettings ?? null;
        $json['pix_settings']                     = $this->pixSettings ?? null;
        return $json;
    }
}
