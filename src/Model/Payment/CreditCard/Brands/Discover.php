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
 * Class Discover
 * @package Woocommerce\Pagarme\Model\Payment\CreditCard\Brands
 */
class Discover extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'discover';

    /** @var string */
    protected $name = 'Discover';

    /** @var int */
    protected $cvv = 4;

    /** @var int[] */
    protected $prefixes = [6011, 622, 64, 65];
}
