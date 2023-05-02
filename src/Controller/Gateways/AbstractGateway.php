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
use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Payment\PostFormatter;
use Woocommerce\Pagarme\Model\WooOrderRepository;
use Woocommerce\Pagarme\Block\Checkout\Gateway as GatewayBlock;

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

    /** @var GatewayBlock */
    private $gatewayBlock;

    /** @var Template*/
    private $template;

    /** @var Yesno */
    protected $yesnoOptions;

    /**
     * @param Gateway|null $gateway
     * @param WooOrderRepository|null $wooOrderRepository
     * @param PostFormatter|null $postFormatter
     * @param Config|null $config
     */
    public function __construct(
        Yesno $yesnoOptions = null,
        Checkout $checkout = null,
        Gateway $gateway = null,
        WooOrderRepository $wooOrderRepository = null,
        PostFormatter $postFormatter = null,
        Config $config = null,
        GatewayBlock $gatewayBlock = null,
        Template $template = null
    ) {
        $this->gatewayBlock = $gatewayBlock ?? new GatewayBlock;
        $this->config = $config ?? new Config;
        $this->postFormatter = $postFormatter ?? new PostFormatter;
        $this->model = $gateway ?? new Gateway;
        $this->checkout = $checkout ?? new Checkout;
        $this->wooOrderRepository = $wooOrderRepository ?? new WooOrderRepository;
        $this->template = $template ?? new Template;
        $this->id = 'woo-pagarme-payments-' . $this->method;
        $this->yesnoOptions = $yesnoOptions ?? new Yesno;
        $this->method_title = $this->getPaymentMethodTitle();
        $this->method_description = __('Payment Gateway Pagar.me', 'woo-pagarme-payments') . ' ' . $this->method_title;
        $this->has_fields = false;
        $this->icon = Core::plugins_url('assets/images/logo.svg');
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
        add_action('woocommerce_thankyou_' . $this->vendor, [$this, 'thank_you_page']);
        add_action('admin_enqueue_scripts', array($this, 'payments_scripts'));
    }

    public function payments_scripts()
    {
        wp_register_script('pagarme_payments', $this->jsUrl('pagarme_payments'), [], false, true);
        wp_enqueue_script('pagarme_payments');
    }

    public function jsUrl($jsFileName)
    {
        return Core::plugins_url('assets/javascripts/admin/' . $jsFileName . '.js');
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
     * @throws \Exception
     */
    public function payment_fields()
    {
        $this->model->payment = $this->method;
        echo $this->gatewayBlock->setPaymentInstance($this->model->getPaymentInstace($this->method))->toHtml();
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
     * @throws \Exception
     */
    public function thank_you_page($order_id)
    {
        $order = $this->wooOrderRepository->getById($order_id);
        $pagarmeOrder = new Order($order_id);
        if ($this->method === $pagarmeOrder->payment_method) {
            $this->template->createBlock(
                '\Woocommerce\Pagarme\Block\Checkout\ThankYou',
                'pagarme.checkout.thank-you',
                [
                    'woo_order' => $order,
                    'pagarme_order' => $pagarmeOrder,
                    'payment_method' => $this->method,
                    'container' => true
                ]
            )->toHtml();
        }
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
        $this->form_fields['enabled'] = $this->field_enabled();
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
        if ($this->isGatewayType()) {
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
        return ($this->model->config->getIsGatewayIntegrationType() === 'yes');
    }

    /**
     * @return array
     */
    public function field_enabled()
    {
        return [
            'title'   => __('Enable/Disable', 'woocommerce'),
            'type'    => 'select',
            'options' => $this->yesnoOptions->toLabelsArray(true),
            'label'   => __('Enable', 'woo-pagarme-payments') . ' ' .
                __($this->getPaymentMethodTitle(), 'woo-pagarme-payments'),
            'default' => __($this->config->getData('enable_' . $this->method), 'woo-pagarme-payments') ?? strtolower(Yesno::NO),
        ];
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
