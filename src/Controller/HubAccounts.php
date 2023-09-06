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

    private $hubAccountErrors;

    public function __construct()
    {
        $this->config = new Config;
        add_action('woocommerce_api_pagarme-account-info', array($this, 'getAccountInfoOnPagarme'));
        add_action('on_pagarme_charge_paid', array($this, 'getAccountIdFromWebhook'));
        if (!Utils::is_request_ajax()) {
            $this->getHubAccountErrorsNotices();
        }
    }

   public function getAccountInfoOnPagarme()
    {
        if (
            empty($this->config->getHubInstallId())
            || empty($this->getAccountId())
        ) {
            return false;
        }
        $accountService = new AccountService();
        try {
            $this->accountInfo = $accountService->getAccount($this->getAccountId());
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Invalid API key') {
                $this->removeHubIntegration();
            }
            return false;
        }
        $this->isDashCorrect();
    }

    public function getAccountId()
    {
        return $this->config->getAccountId() ?? null;
    }

    public function isDashCorrect()
    {
        if (empty($this->accountInfo)) {
            return;
        }
        $this->hubAccountErrors = [];
        $this->isAccountEnabled();
        $this->isDomainCorrect();
        $this->isMultiBuyersEnabled();
        $this->isMultiPaymentsEnabled();
        $this->setHubAccountErrors();
    }

    private function removeHubIntegration()
    {
        $hubCommand = new HubCommand();
        $hubCommand->uninstallCommand();
    }

    public function adminNotices()
    {
        if (empty($this->notices)) {
            return;
        }
        foreach ($this->notices as $notice) {
            wcmpRenderAdminNoticeHtml($notice);
        }
    }

    private function isMultiPaymentsEnabled() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_blwnJNw5umT0QOZk/settings/order-config
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_payments_enabled']) {
            $this->hubAccountErrors[] = 'multiPaymentsDisabled';
            return false;
        }
        return true;
    }

    private function isMultiBuyersEnabled() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_blwnJNw5umT0QOZk/settings/order-config
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_buyers_enabled']) {
            $this->hubAccountErrors[] = 'multiBuyersDisabled';
        }
        return true;
    }

    private function isAccountEnabled()
    {
        if ($this->accountInfo->status != 'active') {
            $this->hubAccountErrors[] = 'accountDisabled';
            return false;
        }
        return true;
    }

    private function isDomainCorrect() // https://dash.pagar.me/merch_1qQzW0iEph7krlAe/acc_6qwpj5RWuEFJaWGY/settings/account-config
    {
        if ($this->config->getIsSandboxMode()) {
            return true;
        }
        $domains = $this->accountInfo->domains;
        if (empty($domains)) {
            $this->hubAccountErrors[] = 'domainEmpty';
            return false;
        }

        $siteUrl = Utils::get_site_url();
        foreach ($domains as $domain){
            if (strpos($siteUrl, $domain) !== false) {
                return true;
            }
        }

        $this->hubAccountErrors[] = 'domainIncorrect';
        return false;
    }

    private function getHubAccountErrorsNotices()
    {
        if ($this->onPagarmePage()) {
            $this->getAccountInfoOnPagarme();
        }

        if (empty($this->getHubAccountErrors())){
            return;
        }

        $noticesList = [
            'multiPaymentsDisabled' => 'Please enable Multipayment option on Dash.',
            'multiBuyersDisabled' => 'Please enable Multibuyers option in Dash.',
            'accountDisabled' => 'Your account is disabled. Please contact the commercial sector to enable it.',
            'domainEmpty' => 'No domain registered. Please enter your website`s domain on Dash.',
            'domainIncorrect' => 'The registered domain is different from the URL of your website. ' .
                'Please correct the domain configured on the Dash.',
        ];

        foreach ($this->hubAccountErrors as $error) {
            $this->notices[] = __($noticesList[$error], 'woo-pagarme-payments');
        }
        add_action('admin_notices', array($this, 'adminNotices'));
    }

    private function setHubAccountErrors()
    {
        $this->config->setData(
            'hub_account_errors',
            $this->hubAccountErrors
        );
        $this->config->save();
    }

    private function getHubAccountErrors()
    {
        $this->hubAccountErrors = $this->config->getData('hub_account_errors');
        return $this->hubAccountErrors;
    }

    private function getAccountIdFromWebhook($body)
    {
        if ($this->getAccountId() || empty($body->account) || empty($body->account->id)){
            return;
        }

        $this->config->setData(
            'account_id',
            $body->account->id
        );
        $this->config->save();
    }

    private function onPagarmePage()
    {
        if (!isset($_GET['page'])) {
            return false;
        };
        if ($_GET['page'] == 'woo-pagarme-payments'){
            return true;
        };
        if (!isset($_GET['section'])){
            return false;
        }
        if ($_GET['page'] == 'wc-settings' && strpos($_GET['section'] , 'woo-pagarme-payments-') !== false) {
            return true;
        }
    }
}
