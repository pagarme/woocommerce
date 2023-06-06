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
 * Class Diners
 * @package Woocommerce\Pagarme\Model\Payment\CreditCard\Brands
 */
class Diners extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'diners';

    /** @var string */
    protected $name = 'Diners';

    /** @var int|int[] */
    protected $size = [14, 16];

    /** @var int[] */
    protected $prefixes = [300, 301, 302, 303, 304, 305, 36, 38];
}
