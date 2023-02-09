<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Controller\Gateways;

use WC_Payment_Gateway;
use WC_Order;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Payment\PostFormatter;
use Woocommerce\Pagarme\Model\WooOrderRepository;

defined( 'ABSPATH' ) || exit;

if (!function_exists('add_action')) {
    exit(0);
}

/**
 * Abstract Gateway
 * @package Woocommerce\Pagarme\Controller\Gateways
 */
abstract class AbstractGateway extends WC_Payment_Gateway
{
    /** @var string */
    const PAGARME = 'Pagar.me';

    /** @var string */
    const PAYMENT_OPTION_UPDATE_SLUG = 'woocommerce_update_options_payment_gateways_';

    /** @var Gateway|null */
    public $model;

    /** @var string */
    protected $method = 'payment';

    /**
     * @var bool
     */
    protected $gatewayType = false;

    /** @var string */
    protected $vendor = self::PAGARME;

    /** @var WooOrderRepository */
    private $wooOrderRepository;

    /** @var PostFormatter */
    private $postFormatter;

    /** @var Config */
    protected $config;

    /** @var Checkout */
    private $checkout;

    /**
     * @param Gateway|null $gateway
     * @param WooOrderRepository|null $wooOrderRepository
     * @param PostFormatter|null $postFormatter
     * @param Config|null $config
     */
    public function __construct(
        Checkout $checkout = null,
        Gateway $gateway = null,
        WooOrderRepository $wooOrderRepository = null,
        PostFormatter $postFormatter = null,
        Config $config = null
    ) {
        if (!$gateway) {
            $gateway = new Gateway();
        }
        if (!$wooOrderRepository) {
            $wooOrderRepository = new WooOrderRepository();
        }
        if (!$postFormatter) {
            $postFormatter = new PostFormatter();
        }
        if (!$config) {
            $config = new Config();
        }
        if (!$checkout) {
            $checkout = new Checkout;
        }

        $this->config = $config;
        $this->postFormatter = $postFormatter;
        $this->model = $gateway;
        $this->checkout = $checkout;
        $this->wooOrderRepository = $wooOrderRepository;
        $this->id = 'woo-pagarme-payments-' . $this->method;
        $this->method_title = $this->getPaymentMethodTitle();
        $this->method_description = __('Payment Gateway Pagar.me', 'woo-pagarme-payments') . ' ' . $this->method_title;
        $this->has_fields = false;
        $this->icon = Core::plugins_url('assets/images/logo.png');
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled', 'no');
        $this->title = $this->getTitle();
        $this->has_fields = true;
        if (is_admin()) {
            add_action(self::PAYMENT_OPTION_UPDATE_SLUG . $this->id, [$this, 'beforeProcessAdminOptions']);
            add_action(self::PAYMENT_OPTION_UPDATE_SLUG . $this->id, [$this, 'process_admin_options']);
        }
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_thankyou_' . $this->vendor . ' ' . $this->method_title, [$this, 'thank_you_page']);
    }

    /**
     * @param $orderId
     * @return array
     * @throws \Exception
     */
    public function process_payment($orderId): array
    {
        $wooOrder = $this->wooOrderRepository->getById($orderId);
//        $this->postFormatter->format($orderId);
        $this->postFormatter->assemblePaymentRequest();
        $this->checkout->process($wooOrder);
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($wooOrder)
        ];
    }

    /**
     * @return void
     */
    public function payment_fields()
    {
        $this->model->payment = $this->method;
        echo (Utils::get_template_as_string(
            'templates/checkout/default',
            ['model' => $this->model]
        ));
    }

    /**
     * @param $order_id
     * @return void
     */
    public function receipt_page($order_id)
    {
        $this->checkout_transparent($order_id);
    }

    /**
     * @param $order_id
     * @return void
     */
    public function checkout_transparent($order_id)
    {
        $wc_order = $this->wooOrderRepository->getById($order_id);
        require_once Core::get_file_path($this->method . '-item.php', 'templates/checkout/');
    }

    /**
     * @param $order_id
     * @return void
     */
    public function thank_you_page($order_id)
    {
        $order = $this->wooOrderRepository->getById($order_id);
        require_once Core::get_file_path('thank-you-page.php', 'templates/');
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        if ($title = $this->get_option('title')) {
            return $title;
        }
        return $this->getPaymentMethodTitle();
    }

    /**
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        return __(ucwords(str_replace('-', ' ', str_replace('_', ' ', $this->method))), 'woo-pagarme-payments');
    }

    /**
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields['title'] = $this->field_title();
        $this->form_fields = array_merge( $this->form_fields, $this->append_form_fields(), $this->append_gateway_form_fields());
    }

    /**
     * @return array
     */
    public function append_form_fields()
    {
        return [];
    }

    /**
     * @return array
     */
    private function append_gateway_form_fields()
    {
        if ($this->model->config->getIsGatewayIntegrationType()) {
            return $this->gateway_form_fields();
        }
        return [];
    }

    /**
     * @return array
     */
    protected function gateway_form_fields()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isGatewayType(){
        return $this->gatewayType;
    }

    /**
     * @return array
     */
    public function field_title()
    {
        return [
            'title'       => __('Checkout title', 'woo-pagarme-payments'),
            'description' => __('Name shown to the customer in the checkout page.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'default'     => __($this->getPaymentMethodTitle(), 'woo-pagarme-payments'),
        ];
    }

    /**
     * @return void
     */
    public function beforeProcessAdminOptions()
    {
        foreach ($_POST as $key => $value) {
            $paymentOptionsSlug = 'woocommerce_'  . $this->id;
            if (strpos($key, $paymentOptionsSlug) !== false) {
                if (array_key_exists(1, explode($paymentOptionsSlug . '_', $key))) {
                    $field = explode($paymentOptionsSlug . '_', $key)[1];
                    if ($field === 'title') {
                        $field = $this->method . '_' . $field;
                    }
                    if ($field === 'enabled') {
                        $field = $this->form_fields['enabled']['old_name'] ?? 'enable_' . $this->method;
                    }
                    $this->config->setData($field, $value);
                }
            }
        }
        $this->config->save();
    }
}
