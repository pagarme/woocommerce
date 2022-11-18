<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\CreditCard;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Brands
 * @package Woocommerce\Pagarme\Model\Payment\Voucher
 */
abstract class AbstractBrands
{
    /** @var string */
    protected $code = '';

    /** @var string */
    protected $name = '';

    /**
     * @return string
     */
    public function getBrandCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
