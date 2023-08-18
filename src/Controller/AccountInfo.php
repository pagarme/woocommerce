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
        return $accountService->getAccount($accountId);
    }
}
