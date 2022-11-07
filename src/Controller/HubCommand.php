<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Woocommerce\Pagarme\Concrete\WoocommerceCoreSetup as CoreSetup;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Setting;
use Woocommerce\Pagarme\Helper\Utils;

class HubCommand
{
    private $settings;
    private const HTTP_OK = 200;
    private const HTTP_BAD_REQUEST = 400;


    public function __construct()
    {
        $this->settings = Setting::get_instance();
        add_action('woocommerce_api_' . Core::get_hub_command_name(), array($this, 'handle_requests'));
    }

    public function handle_requests()
    {
        $params = Utils::get_json_post_data();

        if (empty($params)) {
            return $this->sendResponse(
                'Empty body received.',
                self::HTTP_BAD_REQUEST
            );
        }

        $hubIntegrationService = new HubIntegrationService();
        try {
            $hubIntegrationService->executeCommandFromPost($params);
        } catch (\Throwable $e) {
            if (!$this->isForce($params)) {
                $this->sendResponse($e->getMessage(), self::HTTP_BAD_REQUEST);
            }
        }

        $command = strtolower($params->command) . 'Command';

        if (!method_exists($this, $command)) {
            $message = "Command $params->command executed successfully";
            return $this->sendResponse($message, self::HTTP_OK);
        }

        $commandMessage = $this->$command();

        return $this->sendResponse($commandMessage, self::HTTP_OK);
    }

    private function isForce($params)
    {
        if (!isset($params->force)) {
            return false;
        }
        return $params->force;
    }

    private function sendResponse($message, $code)
    {
        $responseObject = array(
            'response' => $message
        );

        return wp_send_json($responseObject, $code);
    }

    private function uninstallCommand()
    {
        $keysToClear = [
            'hub_install_id',
            'hub_environment',
            'production_secret_key',
            'production_public_key',
            'sandbox_secret_key',
            'sandbox_public_key',
            'environment'
        ];

        foreach ($keysToClear as $key) {
            $this->settings->set(
                $key,
                null
            );
        }

        return 'Hub uninstalled successfully';
    }
}
