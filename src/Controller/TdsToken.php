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
}
