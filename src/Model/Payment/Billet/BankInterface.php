<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Billet;

defined( 'ABSPATH' ) || exit;

/**
 * Interface BankInterface
 * @package Woocommerce\Pagarme\Model\Payment\Billet
 */
interface BankInterface
{
    /**
     * @return int
     */
    public function getBankId();

    /**
     * @return string
     */
    public function getName();
}
