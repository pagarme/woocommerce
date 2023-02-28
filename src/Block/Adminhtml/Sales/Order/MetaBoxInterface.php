<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales\Order;

defined( 'ABSPATH' ) || exit;

/**
 * Interface MetaBoxInterface
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order
 */
interface MetaBoxInterface
{
    /** return string */
    public function getCode();

    /** return int */
    public function getSortOrder();

    /**
     * @return string|null
     */
    public function getTitle();
}
