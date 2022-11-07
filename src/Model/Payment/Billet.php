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
 *  Class Billet
 * @package Woocommerce\Pagarme\Model\Payment
 */
class Billet extends AbstractPayment implements PaymentInterface
{
    /** @var int */
    protected $suffix = 5;

    /** @var string */
    protected $name = 'Billet';

    /** @var string */
    protected $code = 'billet';
}
