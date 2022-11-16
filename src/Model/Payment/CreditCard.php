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
 *  Class Voucher
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Voucher extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 3;

    /** @var string */
    protected $name = 'CreditCard';

    /** @var string */
    protected $code = 'credit-card';
}
