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
use Woocommerce\Pagarme\Controller\Checkout;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
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
    /** @var Gateway|null */
    public $model;

    /** @var string */
    protected $method = 'payment';

    /** @var string */
    protected $vendor = 'Pagar.me';

    /** @var WooOrderRepository */
    private $wooOrderRepository;

    /** @var PostFormatter */
    private $postFormatter;

    /**
     * @param Gateway|null $gateway
     * @param WooOrderRepository|null $wooOrderRepository
     * @param PostFormatter|null $postFormatter
     */
    public function __construct(
        Gateway $gateway = null,
        WooOrderRepository $wooOrderRepository = null,
        PostFormatter $postFormatter = null
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
        $this->postFormatter = $postFormatter;
        $this->model = $gateway;
        $this->wooOrderRepository = $wooOrderRepository;
        $this->id = 'woo-pagarme-payments-' . $this->method;
        $this->method_title = __($this->getPaymentMethodTitle(), 'woo-pagarme-payments');
        $this->method_description = __('Payment Gateway Pagar.me', 'woo-pagarme-payments') . ' ' . $this->getPaymentMethodTitle();
        $this->has_fields = false;
        $this->icon = Core::plugins_url('assets/images/logo.png');
        $this->init_form_fields();
        $this->form_fields = array_merge($this->form_fields,$this->append_form_fields());
        $this->init_settings();
        $this->enabled = $this->get_option('enabled', 'no');
        $this->title = $this->getTitle();
        $this->has_fields = true;
        if (is_admin()) {
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_thankyou_' . $this->vendor, array($this, 'thank_you_page'));
    }

    /**
     * @param $orderId
     * @return array
     * @throws \Exception
     */
    public function process_payment($orderId): array
    {
        $wooOrder = $this->wooOrderRepository->getById($orderId);
        $this->postFormatter->format($orderId);
        $checkout = new Checkout();
        $checkout->process_checkout_transparent($wooOrder);
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($wooOrder)
        ];
    }

    public function payment_fields()
    {
        $this->model->payment = $this->method;
        echo (Utils::get_template_as_string(
            'templates/checkout/default',
            array(
                'model' => $this->model,
            )
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
        $wc_order = new WC_Order($order_id);
        require_once Core::get_file_path($this->method . '-item.php', 'templates/checkout/');
    }

    /**
     * @param $order_id
     * @return void
     */
    public function thank_you_page($order_id)
    {
        $order = new WC_Order($order_id);
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
        return $this->vendor . ' ' . $this->method;
    }

    /**
     * @return string
     */
    public function getPaymentMethodTitle()
    {
        return ucfirst($this->method);
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'hub_environment' => $this->field_hub_environment(),
            'title' => $this->field_title()
        ];
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
     * @return array
     */
    public function field_hub_environment()
    {
        return array(
            'title' => __('Integration environment', 'woo-pagarme-payments'),
            'type'  => 'hub_environment',
        );
    }

    public function generate_hub_environment_html($key, $data)
    {
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php echo __('Integration environment', 'woo-pagarme-payments'); ?>
            </th>
            <td class="forminp">
                <?php echo esc_attr($this->model->settings->hub_environment); ?>
            </td>
        </tr>
        <?php if (!$this->model->settings->hub_install_id) : ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
            <td class="forminp ">
                <div class="pagarme-message-warning">
                        <span>
                            <?= __('Integration pending', 'woo-pagarme-payments'); ?>
                        </span>
                </div>
            </td>
            </th>
        </tr>
        <?php endif; ?>
        <?php if ($this->model->is_sandbox_mode()) : ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
            <td class="forminp ">
                <div class="pagarme-message-warning">
                        <span>
                            <?= __('Important! This store is linked to the Pagar.me test environment. This environment is intended for integration validation and does not generate real financial transactions.', 'woo-pagarme-payments'); ?>
                        </span>
                </div>
            </td>
            </th>
        </tr>
    <?php endif; ?>
        <?php
        return ob_get_clean();
    }
}
