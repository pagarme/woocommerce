<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Order\Transaction;

defined( 'ABSPATH' ) || exit;

/**
 * Class PaymentDetails
 * @package Woocommerce\Pagarme\Block\Order
 */
class DefaultTransaction extends AbstractTransaction
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/transaction/default';

}
