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

use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Payment\BilletCard;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Class BilletCreditCard
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
class BilletCreditCard extends AbstractGateway
{
    /** @var string */
    protected $method = BilletCard::PAYMENT_CODE;

    /**
     * @return array
     */
    public function field_enabled()
    {
        return array(
            'title'   => __('Enable/Disable', 'woocommerce'),
            'type'     => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label'   => __('Enable multi-means (Boleto + Credit card)', 'woo-pagarme-payments'),
            'old_name'    => 'multimethods_billet_card',
            'default'     => $this->config->getData('multimethods_billet_card') ?? strtolower(Yesno::NO),
            'custom_attributes' => array(
                'data-action'  => 'enable-multimethods-billet-card',
                'data-requires-field' => 'billet-bank',
            ),
        );
    }
}
