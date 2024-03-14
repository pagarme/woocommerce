<?php

namespace Woocommerce\Pagarme\Controller;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
use Pagarme\Core\Middle\Model\Account;
use Woocommerce\Pagarme\Model\CoreAuth;
use Woocommerce\Pagarme\Service\AccountService;
use Pagarme\Core\Middle\Model\Account\PaymentMethodSettings;

class HubAccounts
{
    const PAYMENT_DISABLED_MESSAGE = '%1$s payment method is enabled on your store, but disabled on Pagar.me Dash. '
        . 'Please, access the Dash configurations and enable it to be able to process %1$s payment on your store.';

    private $config;

    /**
     * @var Account
     */
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

        $accountService = new AccountService(new CoreAuth(), new Config());
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
        $this->setPaymentsType();
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
        if (!$this->accountInfo) {
            return null;
        }

        $paymentGatewayTypes = [
            'credit_card' => $this->accountInfo->getCreditCardSettings()->isGateway(),
            'pix' => $this->accountInfo->getPixSettings()->isGateway(),
            'voucher' => $this->accountInfo->getVoucherSettings()->isGateway(),
            'billet' => $this->accountInfo->getBilletSettings()->isGateway()
        ];

        $paymentPSPTypes = [
            'credit_card' => $this->accountInfo->getCreditCardSettings()->isPSP(),
            'pix' => $this->accountInfo->getPixSettings()->isPSP(),
            'voucher' => $this->accountInfo->getVoucherSettings()->isPSP(),
            'billet' => $this->accountInfo->getBilletSettings()->isPSP()
        ];

        $this->config->setData('is_payment_gateway', $paymentGatewayTypes);
        $this->config->setData('is_payment_psp', $paymentPSPTypes);
        $this->config->save();

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
            Account::ACCOUNT_DISABLED => 'Your account is disabled on Pagar.me Dash. '
                . 'Please, contact our support team to enable it.',
            Account::DOMAIN_EMPTY => [
                'message' => 'No domain registered on Pagar.me Dash. Please enter your website\'s domain on the Dash '
                    . 'to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('account-config')
            ],
            Account::DOMAIN_INCORRECT => [
                'message' => 'The registered domain is different from the URL of your website. Please correct the '
                    . 'domain configured on the Dash to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('account-config')
            ],
            Account::WEBHOOK_INCORRECT => [
                'message' => 'The URL for receiving webhook registered in Pagar.me Dash is different from the URL of '
                    . 'your website. Please, click the button below to access the Hub and click the Delete > Confirm '
                    . 'button. Then return to your store and integrate again.',
                'buttons' => [wcmpSingleButtonArray(
                    'View Integration',
                    $this->config->getHubUrl()
                )]
            ],
            Account::MULTIPAYMENTS_DISABLED => [
                'message' => 'Multipayment option is disabled on Pagar.me Dash. Please, access the Dash configurations '
                    . 'and enable it to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('order-config')
            ],
            Account::MULTIBUYERS_DISABLED => [
                'message' => 'Multibuyers option is disabled on Pagar.me Dash. Please, access the Dash configurations '
                    . 'and enable it to be able to process payment in your store.',
                'buttons' => $this->getHubNoticeButtons('order-config')
            ],
            PaymentMethodSettings::PIX_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Pix'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            PaymentMethodSettings::CREDITCARD_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Credit Card'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            PaymentMethodSettings::BILLET_DISABLED => [
                'message' => sprintf(self::PAYMENT_DISABLED_MESSAGE, 'Billet'),
                'buttons' => $this->getHubNoticeButtons('payment-methods')
            ],
            PaymentMethodSettings::VOUCHER_DISABLED => [
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
            $this->accountInfo->getErrors()
        );
        $this->config->save();
    }

    private function getHubAccountErrors()
    {
        $this->hubAccountErrors = $this->config->getData('hub_account_errors');
        return $this->hubAccountErrors;
    }

    public function getAccountIdFromWebhook($body)
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
