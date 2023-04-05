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
        add_action('woocommerce_api_' . Core::get_hub_name(), array($this, 'handle_requests'));
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
                    Core::get_hub_command_url(),
                    Core::get_webhook_url()
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

        $this->settings->setData('sandbox_secret_key', null);
        $this->settings->setData('sandbox_public_key', null);
        $this->settings->setData('environment', null);
        $this->settings->save();
    }
}
