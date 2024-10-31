<?php
/**
 * @author      Open Source Team
 * @copyright   2024 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 * @link        https://pagar.me
 */

declare(strict_types = 1);

namespace Woocommerce\Pagarme\Model\Payment\Voucher\Brands;

use Woocommerce\Pagarme\Model\Payment\Voucher\AbstractBrands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined('ABSPATH') || exit;

/**
 * Class Ticket
 * @package Woocommerce\Pagarme\Model\Payment\Voucher\Brands
 */
class Ticket extends AbstractBrands implements BrandsInterface
{
    /** @var string */
    protected $code = 'ticket';

    /** @var string */
    protected $name = 'Ticket';

    /** @var int[] */
    protected $prefixes = [308513, 602651, 603340, 603342];
}
