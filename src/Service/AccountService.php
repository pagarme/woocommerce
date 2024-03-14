<?php

namespace Woocommerce\Pagarme\Service;

use Pagarme\Core\Middle\Model\Account;
use Pagarme\Core\Middle\Model\Account\StoreSettings;
use Pagarme\Core\Middle\Proxy\AccountProxy;
use WC_Order;
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

    /**
     * @var WC_Order|null
     */
    private $order;

    public function __construct(CoreAuth $coreAuth, Config $config, WC_Order $order = null)
    {
        $this->coreAuth = $coreAuth;
        $this->config = $config;
        $this->order = $order;
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

    /**
     * @param $accountId
     * @return mixed
     */
    private function getAccountOnPagarme($accountId)
    {
        $accountService = new AccountProxy($this->coreAuth);
        return $accountService->getAccount($accountId);
    }
}
