<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\CreditCard\Brands;

use Woocommerce\Pagarme\Model\Payment\CreditCard\AbstractBrands;
use Woocommerce\Pagarme\Model\Payment\CreditCard\BrandsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Amex
 * @package Woocommerce\Pagarme\Model\Payment\CreditCard\Brands
 */
class Amex extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'amex';

    /** @var string */
    protected $name = 'Amex';

    /** @var int[] */
    protected $gaps = [4, 10];

    /** @var int */
    protected $size = 15;

    /** @var int */
    protected $cvv = 4;

    /** @var int[] */
    protected $prefixes = [34, 37];

    /** @var string */
    protected $mask = '/(\\d{1,4})(\\d{1,6})?(\\d{1,5})?/g';
}
