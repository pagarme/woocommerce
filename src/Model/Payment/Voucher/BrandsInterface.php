<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Voucher;

defined( 'ABSPATH' ) || exit;

/**
 * Interface BrandsInterface
 * @package Woocommerce\Pagarme\Model\Payment\Voucher
 */
interface BrandsInterface
{
    /**
     * @return string
     */
    public function getBrandCode();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getConfigDataProvider();
}
