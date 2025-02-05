<?php

namespace Woocommerce\Pagarme\Model;

if (!function_exists('add_action')) {
    exit(0);
}

use Exception;
use Pagarme\Core\Hub\Services\HubIntegrationService;
use ReflectionClass;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Payment\PaymentInterface;

class Gateway
{
    /**
     * Credit Card Installment Type - Single
     *
     * A single settings for all flags.
     *
     */
    const CC_TYPE_SINGLE = 1;

    /**
     * Credit Card Installment Type - By Flag
     *
     * Settings for each flag.
     *
     */
    const CC_TYPE_BY_FLAG = 2;

    /**
     * Credit Card Installment Type - Default 1.0
     *
     * Legacy settings from the previews Pagar.me plugin.
     *
     */
    const CC_TYPE_LEGACY = 3;

    /** @var string */
    const HUB_SANDBOX_ENVIRONMENT = 'Sandbox';

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

        for ($i = 0; $i <= $installmentsAmount; ++$i) {
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
            Core::getHubUrl(),
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
    public function getPaymentInstance($paymentCode)
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
