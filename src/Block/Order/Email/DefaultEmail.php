<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order\Email;

defined('ABSPATH') || exit;

/**
 * Class PaymentDetails
 * @package Woocommerce\Pagarme\Block\Order
 */
class DefaultEmail extends AbstractEmail
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/email/default';

}
