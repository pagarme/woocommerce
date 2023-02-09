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
    public function append_form_fields()
    {
        return [
            'multimethods_billet_card' => $this->field_multimethods_billet_card(),
        ];
    }

    /**
     * @return array
     */
    public function field_multimethods_billet_card()
    {
        return array(
            'title'   => __('Multi-means </br>(Boleto + Credit card)', 'woo-pagarme-payments'),
            'type'    => 'checkbox',
            'label'   => __('Enable multi-means (Boleto + Credit card)', 'woo-pagarme-payments'),
            'default' => 'no',
            'custom_attributes' => array(
                'data-action'  => 'enable-multimethods-billet-card',
                'data-requires-field' => 'billet-bank',
            ),
        );
    }
}
