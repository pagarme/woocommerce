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
    const ACCOUNT_DISABLED = 'accountDisabled';
    const DOMAIN_EMPTY = 'domainEmpty';
    const DOMAIN_INCORRECT = 'domainIncorrect';
    const WEBHOOK_INCORRECT = 'webhookIncorrect';
    const MULTIPAYMENTS_DISABLED = 'multiPaymentsDisabled';
    const MULTIBUYERS_DISABLED = 'multiBuyersDisabled';
    const PIX_DISABLED = 'pixDisabled';
    const CREDIT_CARD_DISABLED = 'creditCardDisabled';
    const BILLET_DISABLED = 'billetDisabled';
    const VOUCHER_DISABLED = 'voucherDisabled';
    const PAYMENT_DISABLED_MESSAGE = '%1$s payment method is enabled on your store, but disabled on Pagar.me Dash. '
        . 'Please, access the Dash configurations and enable it to be able to process %1$s payment on your store.';

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
            if ($e->getMessage() === 'Invalid API key') {
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

    public function getMerchantId()
    {
        return $this->config->getMerchantId() ?? null;
    }

    public function isDashCorrect()
    {
        if (empty($this->accountInfo)) {
            return;
        }
        $this->hubAccountErrors = [];
        $this->setPaymentsType();
        $this->isAccountEnabled();
        $this->isDomainCorrect();
        $this->isWebHookCorrect();
        $this->isMultiBuyersEnabled();
        $this->isMultiPaymentsEnabled();
        $this->isPixEnabled();
        $this->isCreditCardEnabled();
        $this->isBilletEnabled();
        $this->isVoucherEnabled();
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
            if (is_array($notice)) {
                wcmpRenderAdminNoticeHtml(
                    __($notice['message'], 'woo-pagarme-payments'),
                    $notice['buttons'],
                    'error',
                    true);
                continue;
            }
            wcmpRenderAdminNoticeHtml(__($notice, 'woo-pagarme-payments'));
        }
    }

    public function setPaymentsType()
    {
        $paymentTypes = [
            'credit_card' => 'creditCardSettings',
            'pix' => 'pixSettings',
            'voucher' => 'voucherSettings',
            'billet' => 'boletoSettings'
        ];

        if (!$this->accountInfo) {
            return null;
        }
        foreach ($paymentTypes as $key => $paymentType) {
            $paymentSettings = $this->accountInfo->$paymentType ?? null;
            $paymentGateway[$key] = ($paymentSettings['gateway'] ?? '') === 'mundipagg';
            $paymentEnabled[$key] = $paymentSettings['enabled'] ?? false;
        }
        $this->config->setData('is_payment_gateway', $paymentGateway);
        $this->config->setData('is_payment_enabled', $paymentEnabled);
        $this->config->save();

    }

    private function isMultiPaymentsEnabled()
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_payments_enabled']) {
            $this->hubAccountErrors[] = self::MULTIPAYMENTS_DISABLED;
            return false;
        }
        return true;
    }

    private function isMultiBuyersEnabled()
    {
        $orderSettings = $this->accountInfo->orderSettings;
        if (!$orderSettings['multi_buyers_enabled']) {
            $this->hubAccountErrors[] = self::MULTIBUYERS_DISABLED;
        }
        return true;
    }

    private function isAccountEnabled()
    {
        if ($this->accountInfo->status !== 'active') {
            $this->hubAccountErrors[] = self::ACCOUNT_DISABLED;
            return false;
        }
        return true;
    }

    private function isDomainCorrect()
    {
        if ($this->config->getIsSandboxMode()) {
            return true;
        }
        $domains = $this->accountInfo->domains;
        if (empty($domains)) {
            $this->hubAccountErrors[] = self::DOMAIN_EMPTY;
            return false;
        }

        $siteUrl = Utils::get_site_url();
        foreach ($domains as $domain){
            if (strpos($siteUrl, $domain) !== false) {
                return true;
            }
        }

        $this->hubAccountErrors[] = self::DOMAIN_INCORRECT;
        return false;
    }

    private function isWebHookCorrect()
    {
        foreach ( $this->accountInfo->webhookSettings as $webhook){
            if (strpos($webhook->url, Utils::get_site_url()) !== false) {
                return true;
            }
        }
        $this->hubAccountErrors[] = self::WEBHOOK_INCORRECT;
        return false;
    }

    private function isPixEnabled()
    {
        $storePixEnabled = $this->config->getData('enable_pix') === 'yes';
        $dashPixDisabled = !$this->accountInfo->pixSettings['enabled'];
        if ($dashPixDisabled && $storePixEnabled) {
            $this->hubAccountErrors[] = self::PIX_DISABLED;
        }
        return true;
    }

    private function isCreditCardEnabled()
    {
        $storeCreditCardEnabled = $this->config->getData('enable_credit_card') === 'yes'
            || $this->config->getData('multimethods_billet_card') === 'yes'
            || $this->config->getData('multimethods_2_cards') === 'yes';
        $dashCreditCardDisabled = !$this->accountInfo->creditCardSettings['enabled'];
        if ($dashCreditCardDisabled && $storeCreditCardEnabled) {
            $this->hubAccountErrors[] = self::CREDIT_CARD_DISABLED;
        }
        return true;
    }

    private function isBilletEnabled()
    {
        $storeBilletEnabled = $this->config->getData('enable_billet') === 'yes'
            || $this->config->getData('multimethods_billet_card') === 'yes';
        $dashBilletDisabled = !$this->accountInfo->boletoSettings['enabled'];
        if ($dashBilletDisabled && $storeBilletEnabled) {
            $this->hubAccountErrors[] = self::BILLET_DISABLED;
        }
        return true;
    }

    private function isVoucherEnabled()
    {
        $storeVoucherEnabled = $this->config->getData('enable_voucher') === 'yes';
        $dashVoucherDisabled = !$this->accountInfo->voucherSettings['enabled'];
        if ($dashVoucherDisabled && $storeVoucherEnabled) {
            $this->hubAccountErrors[] = self::VOUCHER_DISABLED;
        }
        return true;
    }

    /**
     * @param string $dashPage
     * @return array
     */
    private function getHubNoticeButtons($dashPage)
    {
        $buttons = [];
        $dashUrl = $this->config->getDashUrl();
        if ($dashUrl) {
            $dashUrl .= "settings/{$dashPage}/";
            $buttons[] = wcmpSingleButtonArray(
                'Access Dash Configurations',
                $dashUrl,
                'primary',
                '_blank'
            );
        }
        if ($this->getAccountId()) {
            $buttons[] = wcmpSingleButtonArray(
                'Verify Dash Configurations',
                '',
                'secondary',
                '',
                'pagarme-get-hub-account-info'
            );
        }
        return $buttons;
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
            self::ACCOUNT_DISABLED => 'Your account is disabled on Pagar.me Dash. '
                . 'Please, contact our support team to enable it.',
            self::DOMAIN_EMPTY => [
                'message' => 'No domain registered on Pagar.me Dash. Please enter your website\'s domain on the Dash '
                    . 'to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('account-config')
            ],
            self::DOMAIN_INCORRECT => [
                'message' => 'The registered domain is different from the URL of your website. Please correct the '
                    . 'domain configured on the Dash to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('account-config')
            ],
            self::WEBHOOK_INCORRECT => [
                'message' => 'The URL for receiving webhook registered in Pagar.me Dash is different from the URL of '
                    . 'your website. Please, click the button below to access the Hub and click the Delete > Confirm '
                    . 'button. Then return to your store and integrate again.',
                'buttons' => [wcmpSingleButtonArray(
                    'View Integration',
                    $this->config->getHubUrl()
                )]
            ],
            self::MULTIPAYMENTS_DISABLED => [
                'message' => 'Multipayment option is disabled on Pagar.me Dash. Please, access the Dash configurations '
                    . 'and enable it to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('order-config')
            ],
            self::MULTIBUYERS_DISABLED => [
                'message' => 'Multibuyers option is disabled on Pagar.me Dash. Please, access the Dash configurations '
                    . 'and enable it to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('order-config')
            ],
            self::PIX_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Pix'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            self::CREDIT_CARD_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Credit Card'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            self::BILLET_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Billet'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            self::VOUCHER_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Voucher'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ]
        ];

        foreach ($this->hubAccountErrors as $error) {
            $this->notices[] = $noticesList[$error];
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
        }
        if ($_GET['page'] == 'woo-pagarme-payments'){
            return true;
        }
        if (!isset($_GET['section'])){
            return false;
        }
        if ($_GET['page'] == 'wc-settings' && strpos($_GET['section'] , 'woo-pagarme-payments-') !== false) {
            return true;
        }
    }
}
