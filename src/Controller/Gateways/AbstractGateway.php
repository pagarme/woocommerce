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

use WC_Admin_Settings;
use WC_Payment_Gateway;
use Woocommerce\Pagarme\Block\Template;

use Woocommerce\Pagarme\Controller\Gateways\Exceptions\InvalidOptionException;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Model\Subscription;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Config\Source\Yesno;
use Woocommerce\Pagarme\Model\Gateway;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Payment\PostFormatter;
use Woocommerce\Pagarme\Model\WooOrderRepository;
use Woocommerce\Pagarme\Block\Checkout\Gateway as GatewayBlock;
use Woocommerce\Pagarme\Block\Order\EmailPaymentDetails;

defined('ABSPATH') || exit;

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

    const WC_PAYMENT_PAGARME = 'woo-pagarme-payments';

    /** @var string */
    const PAYMENT_OPTION_UPDATE_SLUG = 'woocommerce_update_options_payment_gateways_';

    /** @var string  */
    const PAYMENT_OPTIONS_SETTINGS_NAME = 'woocommerce_%s_settings';

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

    /** @var array */
    protected $sendEmailStatus = ['pending', 'on-hold'];

    /**
     * @var Subscription
     */
    private $subscription;

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
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled', 'no');
        $this->title = $this->getTitle();
        $this->has_fields = true;
        if (is_admin()) {
            add_action("update_option", [$this, 'beforeUpdateAdminOptions'], 10, 3);
            add_action("add_option", [$this, 'beforeAddAdminOptions'], 10, 2);
            add_action(self::PAYMENT_OPTION_UPDATE_SLUG . $this->id, [$this, 'process_admin_options']);
        }
        add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        add_action('woocommerce_thankyou_' . $this->id, [$this, 'thank_you_page']);
        add_action('admin_enqueue_scripts', array($this, 'payments_scripts'));
        add_action('woocommerce_email_after_order_table', [$this, 'pagarme_email_payment_info'], 15, 2 );
        $this->subscription = new Subscription($this);
    }

    /**
     * @return boolean
     */
    public function hasSubscriptionSupport(): bool
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isSubscriptionActive(): bool
    {
        return false;
    }

    public function payments_scripts()
    {
        wp_register_script('pagarme_payments', $this->jsUrl('pagarme_payments'), [], false, true);
        wp_register_script('pagarme_payments_validation', $this->jsUrl('pagarme_payments_validation'), [], false, true);
        wp_enqueue_script('pagarme_payments');
        wp_enqueue_script('pagarme_payments_validation');
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
        if ($this->subscription->isChangePaymentSubscription()) {
            return $this->subscription->processChangePaymentSubscription($wooOrder);
        }
        $this->postFormatter->assemblePaymentRequest();
        if ($this->subscription->hasSubscriptionFreeTrial()) {
            return $this->subscription->processFreeTrialSubscription($wooOrder);
        }
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
     * @param $orderId
     * @return void
     */
    public function receipt_page($orderId)
    {
        $this->checkout_transparent($orderId);
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
        $this->form_fields = array_merge(
            $this->form_fields,
            $this->append_form_fields(),
            $this->append_gateway_form_fields()
        );
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
    public function isGatewayType()
    {
        $isPaymentGateway = $this->model->config->getIsPaymentGateway();
        if (empty($isPaymentGateway) || !key_exists($this->method, $isPaymentGateway)) {
            return $this->model->config->getIsGatewayIntegrationType();
        }
        return $isPaymentGateway[$this->method];
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
            'default' => __(
                $this->config->getData('enable_' . $this->method),
                'woo-pagarme-payments'
                ) ?? strtolower(Yesno::NO),
        ];
    }

    /**
     * @return array
     */
    public function field_title()
    {
        return [
            'title'       => __('Checkout title', 'woo-pagarme-payments'),
            'type'        => 'text',
            'description' => __('Name shown to the customer in the checkout page.', 'woo-pagarme-payments'),
            'desc_tip'    => true,
            'default'     => __($this->getPaymentMethodTitle(), 'woo-pagarme-payments'),
        ];
    }

    /**
     * @param mixed $optionName
     * @param mixed $oldValue
     * @param mixed $values
     * @return void
     */
    public function beforeUpdateAdminOptions($optionName, $oldValue, $values)
    {
        $isValidOption = $optionName !== sprintf(self::PAYMENT_OPTIONS_SETTINGS_NAME, $this->id);
        if ($isValidOption) {
            return;
        }

        $this->saveAdminOptionsInCoreConfig($values);
    }

    /**
     * @param mixed $optionName
     * @param mixed $values
     * @return void
     */
    public function beforeAddAdminOptions($optionName, $values)
    {
        $isValidOption = $optionName !== sprintf(self::PAYMENT_OPTIONS_SETTINGS_NAME, $this->id);
        if ($isValidOption) {
            return;
        }

        $this->saveAdminOptionsInCoreConfig($values);
    }

    /**
     * @param array $values
     * @return void
     */
    protected function saveAdminOptionsInCoreConfig($values)
    {
        foreach ($values as $field => $value) {
            if ($field === 'title') {
                $field = $this->method . '_' . $field;
            }
            if ($field === 'enabled') {
                $field = $this->form_fields['enabled']['old_name'] ?? 'enable_' . $this->method;
            }
            $this->config->setData($field, $value);
        }
        $this->config->save();
    }

    /**
     * @param mixed $order
     * @return void
     */
    public function pagarme_email_payment_info($order, $sent_to_admin)
    {
        if ($sent_to_admin
            || $this->id !== $order->payment_method
            || !in_array($order->get_status(), $this->sendEmailStatus)) {
            return;
        }

        $paymentDetails = new EmailPaymentDetails();
        $paymentDetails->render($order->get_id());
    }

    /**
     * @throws InvalidOptionException
     */
    protected function validateMaxLength($value, $fieldName, $maxLength)
    {
        $isValueLengthGreaterThanMaxLength = mb_strlen($value) > $maxLength;
        if ($isValueLengthGreaterThanMaxLength) {
            $maximumLengthErrorMessage = sprintf(
                __('%s has exceeded the %d character limit.', 'woo-pagarme-payments'),
                __($fieldName, 'woo-pagarme-payments'),
                $maxLength
            );
            $this->addValidationError($maximumLengthErrorMessage);
        }
    }

    /**
     * @throws InvalidOptionException
     */
    protected function validateRequired($value, $fieldName)
    {
        $isValueEmpty = empty($value) && $value !== "0";
        if ($isValueEmpty) {
            $requiredErrorMessage = sprintf(
                __('%s is required.', 'woo-pagarme-payments'),
                __($fieldName, 'woo-pagarme-payments')
            );
            $this->addValidationError($requiredErrorMessage);
        }
    }

    /**
     * @throws InvalidOptionException
     */
    protected function validateMinValue($value, $fieldName, $minValue)
    {
        $isValueLesserThanMinimum = floatval($value) < $minValue;
        if ($isValueLesserThanMinimum) {
            $minimumValueErrorMessage = sprintf(
                __('%s does not have the minimum value of %d.', 'woo-pagarme-payments'),
                __($fieldName, 'woo-pagarme-payments'),
                $minValue
            );
            $this->addValidationError($minimumValueErrorMessage);
        }
    }

    /**
     * @throws InvalidOptionException
     */
    protected function validateAlphanumericAndSpacesAndPunctuation($value, $fieldName)
    {
        if (!empty($value) && !preg_match('/^[A-Za-z0-9À-ú \-:()%@*_.,!?$;]+$/', $value)) {
            $alphanumericAndSpacesAndPunctuationErrorMessage = sprintf(
                __(
                    '%s must only contain letters, numbers, spaces and punctuations (except quotation marks).',
                    'woo-pagarme-payments'
                ),
                __($fieldName, 'woo-pagarme-payments')
            );
            $this->addValidationError($alphanumericAndSpacesAndPunctuationErrorMessage);
        }
    }

    /**
     * @throws InvalidOptionException
     */
    protected function addValidationError($errorMessage)
    {
        WC_Admin_Settings::add_error($errorMessage);
        throw new InvalidOptionException(InvalidOptionException::CODE, $errorMessage);
    }
}
