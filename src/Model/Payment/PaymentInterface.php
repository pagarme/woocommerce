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

defined( 'ABSPATH' ) || exit;

/**
 * Interface PaymentInterface
 * @package Woocommerce\Pagarme\Model\Payment
 */
interface PaymentInterface
{
    /**
     * @return string
     */
    public function getSuffix();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getMethodCode();

    /**
     * @return array
     */
    public function getRequirementsData();

    /**
     * @return array
     */
    public function renameFieldsPost($field, $formattedPost, $arrayFieldKey);
}
