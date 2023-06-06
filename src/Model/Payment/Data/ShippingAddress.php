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
 * Class ShippingAddress
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class ShippingAddress extends AbstractAddress
{
    /** @var string */
    protected $type = 'shipping';
}
