<?php

namespace WooCommerce\Pagarme\Controller;

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Service\TdsTokenService;

class TdsToken
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct()
    {
        $this->config = new Config;
        add_action('woocommerce_api_pagarme-tds-token', [$this, 'getTdsToken']);
        add_action('woocommerce_api_pagarme-tds-token-nx', [$this, 'getTdsTokenNx']);
    }

    public function getTdsToken()
    {
        $accountId = $this->config->getAccountId();
        $tdsTokenService = new TdsTokenService($this->config);
        wp_send_json_success([
            'token' => $tdsTokenService->getTdsToken($accountId)
        ]);
        wp_die();
    }

    public function getTdsTokenNx()
    {
        try {
            $accountId = $this->config->getAccountId();
            $tdsTokenService = new TdsTokenService($this->config);

            $nxToken = $tdsTokenService->getTdsTokenNx($accountId);

            wp_send_json_success([
                'token' => $nxToken,
                'flow_preference' => $nxToken ? 'nx' : 'legacy'
            ]);
        } catch (\Throwable $e) {
            error_log(sprintf(
                'TDS-NX endpoint error: %s in %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            wp_send_json_success([
                'token' => null,
                'flow_preference' => 'legacy'
            ]);
        }
        wp_die();
    }
}
