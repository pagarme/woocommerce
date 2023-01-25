<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Controller\Gateways;

use Woocommerce\Pagarme\Model\Payment\Voucher\Brands;
use Woocommerce\Pagarme\Model\Payment\Voucher\BrandsInterface;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class Voucher
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class TwoCreditCard extends AbstractGateway
{
    /** @var string */
    protected $method = 'two-credit-card';

    /**
     * @return array
     */
    public function field_enabled()
    {
        return [
            'title'   => __('Enable/Disable', 'woocommerce'),
            'type' => 'checkbox',
            'label' => __('Enable multi-means (2 Credit cards)', 'woo-pagarme-payments'),
            'old_name'    => 'multimethods_2_cards',
            'default'     => $this->config->getData('multimethods_2_cards') ?? 'no',
        ];
    }

}
