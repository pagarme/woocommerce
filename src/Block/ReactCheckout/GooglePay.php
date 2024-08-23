<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

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

    public function __construct()
    {
        $paymentModel = new GooglePayModel();
        parent::__construct($paymentModel);
    }

    public function getAdditionalPaymentMethodData()
    {
        return [
            'accountId' => $this->settings['account_id'],
            'merchantName' => $this->settings['googlepay_google_merchant_name'],
            'merchantId' => $this->settings['googlepay_google_merchant_id']
        ];
    }
}
