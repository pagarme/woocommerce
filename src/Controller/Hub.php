<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Exception;

class Hub
{
    private $settings;

    public function __construct()
    {
        $this->settings = new Config();
        add_action('woocommerce_api_' . Core::getHubName(), array($this, 'handle_requests'));
    }

    public function handle_requests()
    {
        $params = $_GET;
        if (isset($params['authorization_code'])) {
            try {
                $hubIntegrationService = new HubIntegrationService();
                $hubIntegrationService->endHubIntegration(
                    $params['install_token'],
                    $params['authorization_code'],
                    Core::getHubCommandUrl(),
                    Core::getWebhookUrl()
                );
                $this->updateConfig();
            } catch (\Throwable $error) {
                throw new Exception($error->getMessage());
            }
        }
        wp_redirect(Core::get_page_link());
    }

    public function updateConfig()
    {
        $moduleConfig = CoreSetup::getModuleConfiguration();

        $this->settings->setData(
            'hub_install_id',
            $moduleConfig->getHubInstallId()->getValue()
        );

        $this->settings->setData(
            'hub_environment',
            $moduleConfig->getHubEnvironment()->getValue()
        );

        $this->settings->setData(
            'production_secret_key',
            $moduleConfig->getSecretKey()->getValue()
        );

        $this->settings->setData(
            'production_public_key',
            $moduleConfig->getPublicKey()->getValue()
        );

        $this->settings->setData(
            'merchant_id',
            $moduleConfig->getMerchantId()->getValue()
        );

        $this->settings->setAccountId($moduleConfig->getAccountId()->getValue());

        $this->settings->setData('sandbox_secret_key', null);
        $this->settings->setData('sandbox_public_key', null);
        $this->settings->setData('environment', null);
        $this->settings->save();
    }
}
