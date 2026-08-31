<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Client;
use Pagarme\Core\Middle\Proxy\TdsTokenProxy;
use Pagarme\Core\Kernel\Services\LogService;
use PagarmeCoreApiLib\Models\GetTdsTokenResponse;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;

class TdsTokenService
{
    /**
     * @var CoreAuth
     */
    private $coreAuth;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->coreAuth = new CoreAuth('');
        $this->config = $config;
    }

    /**
     * @param string $accountId
     * @return string
     */
    public function getTdsToken($accountId)
    {
        $token = $this->getNxToken($accountId);
        if ($token) {
            return $token;
        }

        return $this->getLegacyToken($accountId);
    }

    /**
     * @param string $accountId
     * @return string|null
    */
    private function getNxToken($accountId)
    {
        try {
            $environment = $this->config->getIsSandboxMode() ? 'test' : 'live';
            $url = 'https://hubapi.pagar.me/v2/management/tds-token';

            $response = wp_remote_post($url, [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->config->getSecretKey() . ':'),
                    'Content-Type'  => 'application/json',
                ],
                'body' => json_encode([
                    'merchant_id'  => $accountId
                ]),
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                return null;
            }

            $statusCode = wp_remote_retrieve_response_code($response);
            if ($statusCode !== 200) {
                return null;
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            return $data['tds_token'] ?? null;
        } catch (\Throwable $e) {
            $log = new LogService('TdsTokenService', true);
            $log->error("Failed to fetch NX token: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @param string $accountId
     * @return string
     */
    private function getLegacyToken($accountId)
    {
        $tdsTokenProxy = new TdsTokenProxy($this->coreAuth);
        $environment = $this->config->getIsSandboxMode() ? 'test' : 'live';
        return $tdsTokenProxy->getTdsToken($environment, $accountId)->tdsToken;
    }
}
