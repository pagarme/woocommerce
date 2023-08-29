<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Service\AccountService;

class AccountInfo
{
    private $config;

    public function __construct()
    {
        $this->config = new Config;
        add_action('woocommerce_api_pagarme-account-info', array($this, 'getAccountInfo'));
    }

   public function getAccountInfo()
    {
        $accountService = new AccountService();
        $accountId = "acc_6qwpj5RWuEFJaWGY";
        $response = $accountService->getAccount($accountId);
        $this->isAccountInfoOk();
    }

    public function isAccountInfoOk($response)
    {
        $orderSettings = $response[orderSettings];
        if (!$orderSettings[multi_payments_enabled] || !$orderSettings[multi_buyers_enabled]) {
            $this->showMesg("Erro na Dash");
        }

    }

    private function showMesg(string $string)
    {
        return ;
    }
}
