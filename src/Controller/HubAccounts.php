<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Service\AccountService;

class HubAccounts
{
    private $config;

    private $accountInfo;

    private $notices;

    public function __construct()
    {
        $this->config = new Config;
        // add_action('woocommerce_api_pagarme-account-info', array($this, 'getAccountInfo'));
    }

   public function getAccountInfo()
    {
        if (empty($this->config->getHubInstallId())) {
            return false;
        }
        $accountService = new AccountService();
        try {
            $this->accountInfo = $accountService->getAccount($this->getAccountId());
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Invalid API key') {
            }
            return false;
        }
        $this->isDashCorrect();
    }

    public function getAccountId()
    {
        $accountId = $this->config->getAccountId();
        return $accountId ?? false;
    }

    public function isDashCorrect()
    {
        $this->isAccountEnabled();
        $this->isDomainCorrect();
        $this->isMultiBuyersEnabled();
        $this->isMultiPaymentsEnabled();
        add_action('admin_notices', array($this, 'adminNotices'));
    }

    public function isMultiPaymentsEnabled() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_blwnJNw5umT0QOZk/settings/order-config
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_payments_enabled']) {
            $this->notices[] = __('Please enable Multipayment option on Dash.', 'woo-pagarme-payments');
            return false;
        }
        return true;
    }

    public function isMultiBuyersEnabled() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_blwnJNw5umT0QOZk/settings/order-config
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_buyers_enabled']) {
            $this->notices[] = __('Please enable Multibuyers option in Dash.', 'woo-pagarme-payments');
        }
        return true;
    }

    function adminNotices()
    {
        if (!$this->notices) {
            return;
        }
        foreach ($this->notices as $notice) {
            wcmpRenderAdminNoticeHtml($notice);
        }
    }

    public function isAccountEnabled()
    {
        if ($this->accountInfo->status != 'active') {
            $this->notices[] = __('Your account is disabled. Please contact the commercial sector to enable it.', 'woo-pagarme-payments');
            return false;
        }
        return true;
    }

    public function isDomainCorrect() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_6qwpj5RWuEFJaWGY/settings/account-config
    {
        if ($this->config->getIsSandboxMode()) {
           return true;
        }
        $domains = $this->accountInfo->domains;
        if (empty($domains)) {
            $this->notices[] = __('No domain registered. Please enter your website`s domain on Dash.', 'woo-pagarme-payments');
            return false;
        }

        $siteUrl = Utils::get_site_url();
        foreach ($domains as $domain){
            if (strpos($siteUrl, $domain) !== false) {
                return true;
            }
        }

        $this->notices[] = __('The registered domain is different from the URL of your website. ' .
            'Please correct the domain configured on the Dash.', 'woo-pagarme-payments');
        return false;
    }
}
