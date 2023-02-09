<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Controller\Gateways;

use Woocommerce\Pagarme\Model\Payment\TwoCards;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class TwoCreditCard
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class TwoCreditCard extends AbstractGateway
{
    /** @var string */
    protected $method = TwoCards::PAYMENT_CODE;
}
