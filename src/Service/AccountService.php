<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Model\Account;
use Pagarme\Core\Middle\Model\Account\StoreSettings;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use Woocommerce\Pagarme\Helper\Utils;
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

    /**
     * @param mixed $accountId
     * @return Account
     */
    public function getAccount($accountId)
    {
        $storeSettings = new StoreSettings();
        $storeSettings->setSandbox($this->config->getIsSandboxMode());
        $storeSettings->setStoreUrls(
            [Utils::get_site_url()]
        );
        $storeSettings->setEnabledPaymentMethods($this->config->availablePaymentMethods());

        $accountResponse = $this->getAccountOnPagarme($accountId);

        $account = Account::createFromSdk($accountResponse);
        return $account->validate($storeSettings);
    }

    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }

}
