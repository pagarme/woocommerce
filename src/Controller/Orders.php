<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order as BlockOrder;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformOrderDecorator;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Services\OrderService;
use Exception, WC_Order;

class Orders
{
    private $settings;

    /** @var BlockOrder */
    private $blockOrder;

    /** @var bool  */
    private $debug;

    public function __construct(
        BlockOrder $blockOrder = null
    ) {
        $this->settings = new Config();
        $this->debug    = $this->settings->getEnableLogs();
        $this->blockOrder = $blockOrder ?? new BlockOrder;
        add_action('on_pagarme_order_paid', array($this, 'set_order_paid'), 20, 2);
        add_action('on_pagarme_order_created', array($this, 'set_order_created'), 20, 2);
        add_action('on_pagarme_order_canceled', array($this, 'set_order_canceled'), 20, 2);
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
    }

    public function create_order(WC_Order $wc_order, $payment_method, $form_fields)
    {
        try {
            WoocommerceCoreSetup::bootstrap();

            $platformOrderDecoratorClass = AbstractModuleCoreSetup::get(
                AbstractModuleCoreSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS
            );

            $platformPaymentMethodDecoratorClass  = AbstractModuleCoreSetup::get(
                AbstractModuleCoreSetup::CONCRETE_PLATFORM_PAYMENT_METHOD_DECORATOR_CLASS
            );

            /** @var WoocommercePlatformOrderDecorator $orderDecorator */
            $orderDecorator = new $platformOrderDecoratorClass($form_fields, $payment_method);
            $orderDecorator->setPlatformOrder($wc_order);

            $paymentMethodDecorator = new $platformPaymentMethodDecoratorClass();
            $paymentMethodDecorator->setPaymentMethod($orderDecorator);

            $orderDecorator->setPaymentMethod($paymentMethodDecorator->getPaymentMethod());
            $orderDecorator->setAttempts($wc_order->get_meta('_pagarme_attempts'));
            $orderService = new OrderService();
            $response = $orderService->createOrderAtPagarme($orderDecorator);

            return array_shift($response);
        } catch (Exception $e) {
            if (!empty($this->settings)) {
                $this->settings->log()->add('woo-pagarme', 'CREATE ORDER ERROR: ' . $e->__toString());
            }
            error_log($e->__toString());
            return null;
        }
    }

    public function set_order_created(Order $order, $body)
    {
        $order->payment_on_hold();
    }

    public function set_order_paid(Order $order, $body)
    {
        $order->payment_paid();
    }

    public function set_order_canceled(Order $order, $body)
    {
        $order->payment_canceled();
    }

    public function add_meta_boxes()
    {
        global $theorder;
        if(empty($theorder)) {
            return;
        }
        $order = new Order($theorder->get_id());
        if ($order->isPagarmePaymentMethod()){
            wp_register_script('pagarme-adminhmlt-order-view-cancel-capture', $this->jsUrl('sales/order/view/cancel-capture'), ['jquery'], false);
            wp_enqueue_script('pagarme-adminhmlt-order-view-cancel-capture');
            foreach ($this->blockOrder->getMetaBoxes() as $metaBox) {
                add_meta_box(
                    $metaBox->getCode(),
                    $metaBox->getTitle(),
                    [$metaBox, 'toHtml'],
                    ['shop_order', 'woocommerce_page_wc-orders'],
                    'advanced',
                    'high'
                );
            }
        }
    }

    public function jsUrl($jsFileName)
    {
        return Core::plugins_url('assets/javascripts/admin/' . $jsFileName . '.js');
    }
}
