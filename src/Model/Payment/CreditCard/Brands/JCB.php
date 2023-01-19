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
 * Class Jcb
 * @package Woocommerce\Pagarme\Model\Payment\CreditCard\Brands
 */
class Jcb extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'jcb';

    /** @var string */
    protected $name = 'Jcb';

    /** @var int[] */
    protected $prefixes = [35, 2131, 1800];
}
