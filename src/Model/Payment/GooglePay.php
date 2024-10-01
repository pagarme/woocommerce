<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

defined( 'ABSPATH' ) || exit;

/**
 *  Class GooglePay
 * @package Woocommerce\Pagarme\Model\Payment
 */
class GooglePay extends AbstractPayment implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'googlepay';

    /** @var string */
    protected $name = 'Google Pay';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

    /** @var string[] */
    protected $requirementsData = [
        'payment_method',
        'token'
    ];

    /** @var array */
    protected $dictionary = [
        'token' => 'token'
    ];
}
