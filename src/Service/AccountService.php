<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Model\Account;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\CoreAuth;

class AccountService
{
    protected $coreAuth;

    /**
     * @var Config
     */
    private $config;

    public function __construct()
    {
        $this->coreAuth = new CoreAuth();
        $this->config = new Config();
    }

    public function getAccount($accountId)
    {
        $account = new Account();
        return $this->getAccountOnPagarme($accountId);
    }

    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }

}
