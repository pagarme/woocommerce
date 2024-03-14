<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

// Exeption
use Exception;

use Pagarme\Core\Hub\Services\HubIntegrationService;
use ReflectionClass;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Payment\PaymentInterface;

// WooCommerce
use WC_Order;

class Gateway
{
    /**
     * Credit Card Installment Type - Single
     *
     * A single settings for all flags.
     *
     */
    const CC_TYPE_SINGLE  = 1;

    /** @var string */
    const HUB_SANDBOX_ENVIRONMENT  = 'Sandbox';

    /**
     * Credit Card Installment Type - By Flag
     *
     * Settings for each flag.
     *
     */
    const CC_TYPE_BY_FLAG = 2;

    /** @var Config|null */
    public $config;

    /**
     * @var string
     */
    public $payment;

    public function __construct(
        Config $config = null
    ) {
        if (!$config) {
            $config = new Config();
        }
        $this->config = $config;
    }

    public function supported_currency()
    {
        return (get_woocommerce_currency() === 'BRL');
    }

    /**
     * @param bool $isGatewayType
     * @return array
     */
    public function getInstallmentOptions($isGatewayType = false)
    {
        $installments = [];
        $installmentsAmount = $this->getInstallmentsMaximumQuantity($isGatewayType);

        for ($i = 1; $i <= $installmentsAmount; ++$i) {
            $installments[$i] = $i;
        }

        return $installments;
    }

    /**
     * @param bool $isGatewayType
     * @return int
     */
    public function getInstallmentsMaximumQuantity($isGatewayType)
    {
        return $isGatewayType ? 24 : 12;
    }

    public function getSoftDescriptorMaxLength($isGatewayType)
    {
        return $isGatewayType ? 22 : 13;
    }

    public function get_installments_by_type($total, $flag = false)
    {
        $flags             = $this->settings->flags;
        $type              = $this->settings->cc_installment_type;
        $max_installments  = intval($this->settings->cc_installments_maximum);
        $min_amount        = Utils::str_to_float($this->settings->cc_installments_min_amount);
        $no_interest       = intval($this->settings->cc_installments_without_interest);
        $interest          = Utils::str_to_float($this->settings->cc_installments_interest);
        $interest_increase = Utils::str_to_float($this->settings->cc_installments_interest_increase);

        $method = '_calc_installments_' . $type;

        return $this->{$method}(
            compact('max_installments', 'min_amount', 'no_interest', 'interest', 'interest_increase', 'total', 'flag')
        );
    }

    /** phpcs:disable */
    public function render_installments_options($total, $max_installments, $min_amount, $interest, $interest_increase, $no_interest)
    {
        $output = sprintf(
            '<option value="1">%1$s</option>',
            __('1x', 'woo-pagarme-payments') . ' (' . wc_price($total) . ')'
        );

        $interestBase = $interest;

        for ($times = 2; $times <= $max_installments; $times++) {
            $interest = $interestBase;
            $amount = $total;

            if ($interest || $interest_increase) {

                if ($interest_increase && $times > $no_interest + 1) {
                    $interest += ($interest_increase * ($times - ($no_interest + 1)));
                }

                $amount += Utils::calc_percentage($interest, $total);
            }

            $value = $amount;

            if ($times <= $no_interest) {
                $value = $total;
            }

            $price = ceil($value / $times * 100) / 100;
            if ($price < $min_amount) {
                break;
            }
            $text  = sprintf(
                __('%dx of %s (%s)', 'woo-pagarme-payments'),
                $times,
                wc_price($price),
                wc_price($value)
            );

            $text .= $this->verifyInterest($times, $no_interest, $interest);

            $output .= sprintf('<option value="%1$s">%2$s</option>', $times, $text);
        }

        return $output;
    }

    /**
    * @param int $times
    * @param mixed $noInterest
    * @param mixed $interest
    * @return string
    */
    public function verifyInterest(int $times, $no_interest, $interest): string
    {
        if ($times > $no_interest && $interest) {
            return " c/juros";
        }

        return " s/juros";
    }

    private function _calc_installments_1(array $params)
    {
        extract($params, EXTR_SKIP);

        return $this->render_installments_options($total, $max_installments, $min_amount, $interest, $interest_increase, $no_interest);
    }

    private function _calc_installments_2(array $params)
    {
        $settings_by_flag = $this->settings->cc_installments_by_flag;

        extract($params, EXTR_SKIP);

        if (!$flag || !isset($settings_by_flag['max_installment'][$flag])) {
            return sprintf('<option value="">%s</option>', __('This card brand not is allowed on checkout.', Core::SLUG));
        }

        $max_installments  = intval($settings_by_flag['max_installment'][$flag]);
        $min_amount        = Utils::str_to_float($settings_by_flag['installment_min_amount'][$flag]);
        $no_interest       = intval($settings_by_flag['no_interest'][$flag]);
        $interest          = Utils::str_to_float($settings_by_flag['interest'][$flag]);
        $interest_increase = Utils::str_to_float($settings_by_flag['interest_increase'][$flag]);

        return $this->render_installments_options($total, $max_installments, $min_amount, $interest, $interest_increase, $no_interest);
    }

    public function get_hub_button_text($hub_install_id)
    {
        return !empty($hub_install_id)
            ? __('View Integration', 'woo-pagarme-payments')
            : __('Integrate With Pagar.me', 'woo-pagarme-payments');
    }

    public function get_hub_url($hub_install_id)
    {
        return !empty($hub_install_id)
            ? $this->get_hub_view_integration_url($hub_install_id)
            : $this->get_hub_integrate_url();
    }

    private function get_hub_app_id()
    {
        return CoreSetup::getHubAppPublicAppKey();
    }

    private function get_hub_integrate_url()
    {
        $baseUrl = sprintf(
            'https://hub.pagar.me/apps/%s/authorize',
            $this->get_hub_app_id()
        );

        $params = sprintf(
            '?redirect=%s?install_token=%s',
            Core::get_hub_url(),
            $this->get_hub_install_token()
        );

        return $baseUrl . $params;
    }

    private function get_hub_view_integration_url($hub_install_id)
    {
        return sprintf(
            'https://hub.pagar.me/apps/%s/edit/%s',
            $this->get_hub_app_id(),
            $hub_install_id
        );
    }

    private function get_hub_install_token()
    {
        $installSeed = uniqid();
        $hubIntegrationService = new HubIntegrationService();
        $installToken = $hubIntegrationService
            ->startHubIntegration($installSeed);

        return $installToken->getValue();
    }

    /**
     * @return bool
     */
    public function is_sandbox_mode(): bool
    {
        return ( $this->settings->hub_environment === static::HUB_SANDBOX_ENVIRONMENT ||
            strpos($this->settings->production_secret_key, 'sk_test') !== false ||
            strpos($this->settings->production_public_key, 'pk_test') !== false
        );
    }

    /**
     * @param $paymentCode
     * @return PaymentInterface
     * @throws Exception
     */
    public function getPaymentInstace($paymentCode)
    {
        foreach ($this->getPayments() as $class) {
            /** @var PaymentInterface $payment */
            $payment = new $class;
            if ($payment->getMethodCode() === $paymentCode) {
                return $payment;
            }
        }
        throw new \Exception(__('Invalid payment method: ', 'woo-pagarme-payments') . $paymentCode);
    }

    /**
     * @return array
     */
    private function getPayments()
    {
        $this->autoLoad();
        $payments = [];
        foreach (get_declared_classes() as $class) {
            try {
                $reflect = new ReflectionClass($class);
                if($reflect->implementsInterface(PaymentInterface::class)) {
                    $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                    $payments[end($explodedFileName)] = $class;
                }
            } catch (\ReflectionException $e) {}
        }
        return $payments;
    }

    private function autoLoad()
    {
        foreach(glob( __DIR__ . '/Payment/*.php') as $file) {
            include_once($file);
        }
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        $jsConfigProvider = [];
        foreach ($this->getPayments() as $class) {
            $payment = new $class;
            $jsConfigProvider['payment'][$payment->getMethodCode()] = $payment->getConfigDataProvider();
        }
        return $jsConfigProvider;
    }
}
