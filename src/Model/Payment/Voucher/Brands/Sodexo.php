<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Voucher\Brands;

use Woocommerce\Pagarme\Model\Payment\Voucher\AbstractBrands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Sodexo
 * @package Woocommerce\Pagarme\Model\Payment\Voucher\Brands
 */
class Sodexo extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'sodexo';

    /** @var string */
    protected $name = 'Sodexo';

    /** @var int[] */
    protected $prefixes = [603389, 606071, 606069, 600818, 606070, 606068];
}
