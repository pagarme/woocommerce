<?php

namespace Woocommerce\Pagarme\Block\ReactCheckout;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Payment\AbstractPayment;

abstract class AbstractPaymentMethodBlock extends AbstractPaymentMethodType
{
    /** @var string */
    const PAYMENT_METHOD_KEY = '';

    /** @var string */
    const ARIA_LABEL = '';

    /** @var AbstractPayment */
    protected $paymentModel;

    public function __construct( AbstractPayment $paymentModel )
    {
        $this->paymentModel = $paymentModel;
    }

    public function initialize()
    {
        $this->settings = $this->paymentModel->getSettings();
    }

    /**
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        $scriptName = sprintf( 'pagarme_payments_%s_blocks_integration', static::PAYMENT_METHOD_KEY );
        wp_register_script( $scriptName, $this->jsUrl(), $this->getScriptDependencies(), false, true );

        return [
            $scriptName
        ];
    }

    /**
     * @return array
     */
    public function get_payment_method_data()
    {
        $paymentData = [
            'name'      => $this->name,
            'key'       => static::PAYMENT_METHOD_KEY,
            'label'     => $this->settings['title'],
            'ariaLabel' => __( static::ARIA_LABEL, 'woo-pagarme-payments' )
        ];

        $additionalPaymentData = $this->getAdditionalPaymentMethodData();

        return array_merge( $paymentData, $additionalPaymentData );
    }

    /**
     * @return string
     */
    protected function jsUrl()
    {
        return Core::plugins_url( 'build/' . static::PAYMENT_METHOD_KEY . '.js' );
    }

    /**
     * @return array
     */
    protected function getScriptDependencies()
    {
        return [ 'wp-components', 'react' ];
    }

    /**
     * @return array
     */
    protected function getAdditionalPaymentMethodData()
    {
        return [];
    }
}
