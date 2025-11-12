<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Pagarme\Core\Hub\Services\HubIntegrationService;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Helper\Utils;

class HubCommand
{
    private $settings;
    private const HTTP_OK = 200;
    private const HTTP_BAD_REQUEST = 400;


    public function __construct()
    {
        $this->settings = new Config;
        add_action('woocommerce_api_' . Core::getHubCommandName(), array($this, 'handle_requests'));
    }

    public function handle_requests()
    {
        if (!Utils::isCurrentUserAdmin()) {
            return $this->sendResponse(
                'You do not have permission to access this resource.',
                self::HTTP_BAD_REQUEST
            );
        }

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

    public function uninstallCommand()
    {
        $keysToClear = [
            'hub_install_id' => null,
            'hub_environment' => null,
            'production_secret_key' => null,
            'production_public_key' => null,
            'sandbox_secret_key' => null,
            'sandbox_public_key' => null,
            'environment' => null,
            'account_id' => null,
            'merchant_id' => null,
            'hub_account_errors' => null
        ];
        $this->settings->addData($keysToClear)->save();
        return 'Hub uninstalled successfully';
    }
}
