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

use Woocommerce\Pagarme\Model\Payment\GooglePay as ModelGooglePay;

defined('ABSPATH') || exit;

if (!function_exists('add_action')) {
    exit(0);
}
class GooglePay extends AbstractGateway
{
    /** @var string */
    protected $method = ModelGooglePay::PAYMENT_CODE;
       /**
     * @return void
     */
    public function addRefundSupport()
    {
        $this->supports[] = 'refunds';
    }
    /**
     * @return array
     */
    public function append_form_fields()
    {
        $fields = [
            'account_id' => $this->field_account_id(),
            'googlepay_google_merchant_id' => $this->field_googlepay_google_merchant_id(),
            'googlepay_google_merchant_name' => $this->field_googlepay_google_merchant_name(),
        ];
        return $fields;
    }

    public function field_account_id()
    {
        return [
            'title' => __('ID da conta Pagar.me', 'woo-pagarme-payments'),
            'default' => $this->config->getData('account_id') ?? '',
            'type' => 'text',
            'description'     => sprintf( __( 'Consulte em: <a href="%s">Configurações &rarr; Chaves &rarr; ID da Conta »</a>', 'woocommerce' ), "#" ),
        ];
    }

    public function field_googlepay_google_merchant_id()
    {
        return [
            'title' => __('MerchantId Google Pay', 'woo-pagarme-payments'),
            'default' => '',
            'type' => 'text',
            'description'     => sprintf( __( 'Identificador de comerciante do Google, adiquira o seu <a href="%s">aqui</a>.', 'woocommerce' ), "#" ),
        ];
    }

    public function field_googlepay_google_merchant_name()
    {
        return [
            'title' => __('Nome da loja na Google Pay', 'woo-pagarme-payments'),
            'default' => '',
            'desc_tip' => true,
            'type' => 'text',
            'desc'     => __( 'Nome da sua loja que será exibido ao cliente enquanto compra através do Google Pay.', 'woocommerce' ),
        ];
    }
}
