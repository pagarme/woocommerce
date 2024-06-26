<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model;

use WC_Order;
use Woocommerce\Pagarme\Model\Payment\PaymentInterface;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Payment
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Payment
{
    /** @var Gateway */
    private $gateway;

    /**
     * @var PaymentInterface
     */
    protected $paymentInstance;

    /**
     * @param string $paymentMethod
     * @param Gateway|null $gateway
     * @throws \Exception
     */
    public function __construct(
        string $paymentMethod,
        Gateway $gateway = null
    ) {
        if (!$gateway) {
            $gateway = new Gateway;
        }
        $this->gateway = $gateway;
        $this->paymentInstance = $this->gateway->getPaymentInstance($paymentMethod);
    }

    /**
     * Return the payment array for API request
     * @return array
     */
    public function get_payment_data(WC_Order $wc_order, array $form_fields, $customer = null)
    {
        return $this->paymentInstance->getPayRequest($wc_order, $form_fields, $customer);
    }
}
