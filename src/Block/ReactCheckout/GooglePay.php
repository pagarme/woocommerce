<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\Payment\GooglePay as GooglePayModel;

class GooglePay extends AbstractPaymentMethodBlock
{
    /** @var string */
    protected $name = 'woo-pagarme-payments-googlepay';

    /** @var string */
    const PAYMENT_METHOD_KEY = 'googlepay';

    const GOOGLE_BRANDS = ['VISA', 'ELECTRON', 'MASTERCARD', 'MAESTRO', 'ELO'];
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

    private function getGooglepayBrands()
    {
        $allowedBrands = [];
        foreach ($this->config->getCcFlags() as $brand) {
            $brand = strtoupper($brand);
            if( in_array($brand, self::GOOGLE_BRANDS) ) {
                $allowedBrands[] = $brand;
            }
        }
        return $allowedBrands;
    }

    /**
     * @return boolean
     */
    protected function jsUrl()
    {
        return false;
    }
    
    public function getAdditionalPaymentMethodData()
    {
        return [
            'enabled' => $this->config->getEnableGooglepay() === 'yes', 
            'accountId' => $this->config->getAccountId(),
            'merchantName' => $this->config->getGooglepayGoogleMerchantName(),
            'merchantId' => $this->config->getGooglepayGoogleMerchantId(),
            'isSandboxMode' => $this->config->getIsSandboxMode(),
            'allowedGoogleBrands' => $this->getGooglepayBrands(),
            'hasSubscriptionInCart' => Subscription::hasSubscriptionProductInCart()
        ];
    }
}
