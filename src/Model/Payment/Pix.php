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
 *  Class Pix
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Pix extends AbstractPaymentWithCheckoutInstructions implements PaymentInterface
{
    /** @var string */
    const PAYMENT_CODE = 'pix';

    /** @var string */
    const IMAGE_FILE_NAME = 'pix.svg';

    /** @var int */
    protected $suffix = 7;

    /** @var string */
    protected $name = 'Pix';

    /** @var string */
    protected $code = self::PAYMENT_CODE;

    /** @var string[] */
    protected $requirementsData = [
        'multicustomer_pix',
        'payment_method',
        'enable_multicustomers_pix'
    ];

    /** @var array */
    protected $dictionary = [];

    /**
     * @return string
     */
    public static function getDefaultCheckoutInstructions()
    {
        return __(
            'The QR Code for your payment with PIX will be generated after confirming the purchase. '
            . 'Point your phone at the screen to capture the code or copy and paste the code into your '
            . 'payments app.',
            'woo-pagarme-payments'
        );
    }
}
