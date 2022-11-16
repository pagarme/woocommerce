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

use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Pix
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Pix extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 7;

    /** @var string */
    protected $name = 'Pix';

    /** @var string */
    protected $code = 'pix';

    /** @var string[] */
    protected $requirementsData = [
        'multicustomer_pix',
        'payment_method',
        'enable_multicustomers_pix'
    ];

    /** @var array */
    protected $dictionary = [];
}
