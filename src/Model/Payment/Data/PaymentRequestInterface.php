<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

defined( 'ABSPATH' ) || exit;

/**
 * Interface PaymentRequestInterface
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
interface PaymentRequestInterface
{
    /** @var string */
    const PAYMENT_METHOD = 'payment_method';

    /** @var string */
    const CARDS = 'cards';

    /** @var string */
    const SHIPPING_METHOD = 'shipping_method';

    /** @var string */
    const SHIPPING_ADDRESS = 'shipping_address';

    /** @var string */
    const BILLING_ADDRESS = 'billing_address';

    /** @var string */
    const PAGARME_PAYMENT_REQUEST_KEY = 'pagarme_payment_request';

    /** @var string */
    const BILLET = 'billet';

    /** @var string */
    const PIX = 'pix';

    /**
     * @param string $value
     * @return PaymentRequest
     */
    public function setPaymentMethod(string $value);

    /**
     * @return string
     */
    public function getPaymentMethod();

    /**
     * @return PaymentRequest
     */
    public function setShippingMethod();

    /**
     * @return array|mixed|null
     */
    public function getShippingMethod();
}
