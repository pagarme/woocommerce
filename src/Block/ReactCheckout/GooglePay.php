<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Payment\GooglePay as GooglePayModel;

class GooglePay extends AbstractPaymentMethodBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-googlepay';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'googlepay';

    /** @var string */
    const ARIA_LABEL = 'Google Pay';

    /** @var GooglePayModel */
    protected $paymentModel;

    /** @var Config */
    protected $config;

    public function __construct()
    {
        $this->config = new Config;
        $paymentModel = new GooglePayModel();
        parent::__construct($paymentModel);
    }

    public function getAdditionalPaymentMethodData()
    {
        return [
            'accountId' => $this->config->getAccountId(),
            'merchantName' => $this->config->getGooglepayGoogleMerchantName(),
            'merchantId' => $this->config->getGooglepayGoogleMerchantId(),
            'isSandboxMode' => $this->config->getIsSandboxMode()
        ];
    }
}
