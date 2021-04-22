<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Exception;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Services\OrderService;
use Woocommerce\Pagarme\Concrete\WoocommercePlatformOrderDecorator;

use WC_Order;

class Api
{
    public static $instance = null;
    public $debug           = false;
    public $settings;

    private function __construct()
    {
        $this->settings = Setting::get_instance();
        $this->debug    = $this->settings->is_enabled_logs();
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

    public static function get_instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
