<?php

namespace Pagarme\Core\Middle\Model;

use Pagarme\Core\Middle\Model\Account\PaymentEnum;
use Pagarme\Core\Middle\Model\Account\PaymentMethodSettings;
use Pagarme\Core\Middle\Model\Account\StoreSettings;
use PagarmeCoreApiLib\Models\GetAccountResponse;

class Account extends ModelWithErrors
{
    const ACCOUNT_DISABLED = 'accountDisabled';

    const MULTIPAYMENTS_DISABLED = 'multiPaymentsDisabled';

    const MULTIBUYERS_DISABLED = 'multiBuyersDisabled';

    /**
     * @var bool
     */
    private $accountEnabled;

    /**
     * @var bool
     */
    private $multiPaymentsEnabled;

    /**
     * @var bool
     */
    private $multiBuyerEnabled;

    /**
     * @var array
     */
    private $domains;

    /**
     * @var array
     */
    private $webhooks;

    /**
     * @var PaymentMethodSettings
     */
    private $creditCardSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $billetSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $pixSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $voucherSettings;

    /**
     * @var PaymentMethodSettings
     */
    private $debitCardSettings;

    /**
     * @return bool
     */
    public function isAccountEnabled()
    {
        return $this->accountEnabled;
    }

    /**
     * @param mixed $accountEnabled
     */
    public function setAccountEnabled($accountEnabled)
    {
        if (is_string($accountEnabled)) {
            $this->accountEnabled = $accountEnabled === 'active';
            return;
        }
        $this->accountEnabled = $accountEnabled;
    }

    /**
     * @return bool
     */
    public function isMultiPaymentsEnabled()
    {
        return $this->multiPaymentsEnabled;
    }

    /**
     * @param bool $multiPaymentsEnabled
     */
    public function setMultiPaymentsEnabled($multiPaymentsEnabled)
    {
        $this->multiPaymentsEnabled = $multiPaymentsEnabled;
    }

    /**
     * @return bool
     */
    public function isMultiBuyerEnabled()
    {
        return $this->multiBuyerEnabled;
    }

    /**
     * @param bool $multiBuyerEnabled
     */
    public function setMultiBuyerEnabled($multiBuyerEnabled)
    {
        $this->multiBuyerEnabled = $multiBuyerEnabled;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @param array $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    /**
     * @return array
     */
    public function getWebhooks()
    {
        return $this->webhooks;
    }

    /**
     * @param array $webhooks
     */
    public function setWebhooks($webhooks)
    {
        $this->webhooks = $webhooks;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getCreditCardSettings()
    {
        return $this->creditCardSettings;
    }

    /**
     * @param PaymentMethodSettings $creditCardSettings
     */
    public function setCreditCardSettings($creditCardSettings)
    {
        $this->creditCardSettings = $creditCardSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getBilletSettings()
    {
        return $this->billetSettings;
    }

    /**
     * @param PaymentMethodSettings $billetSettings
     */
    public function setBilletSettings($billetSettings)
    {
        $this->billetSettings = $billetSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getPixSettings()
    {
        return $this->pixSettings;
    }

    /**
     * @param PaymentMethodSettings $pixSettings
     */
    public function setPixSettings($pixSettings)
    {
        $this->pixSettings = $pixSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getVoucherSettings()
    {
        return $this->voucherSettings;
    }

    /**
     * @param PaymentMethodSettings $voucherSettings
     */
    public function setVoucherSettings($voucherSettings)
    {
        $this->voucherSettings = $voucherSettings;
    }

    /**
     * @return PaymentMethodSettings
     */
    public function getDebitCardSettings()
    {
        return $this->debitCardSettings;
    }

    /**
     * @param PaymentMethodSettings $debitCardSettings
     */
    public function setDebitCardSettings($debitCardSettings)
    {
        $this->debitCardSettings = $debitCardSettings;
    }

    /**
     * @param StoreSettings|null $storeSettings
     * @return $this
     */
    public function validate($storeSettings = null)
    {
        $this->validateAccountEnabled();
        $this->validateMultiBuyer();
        $this->validateMultiPayments();

        if ($storeSettings) {
            $this->addError($this->getCreditCardSettings()->validate($storeSettings));
            $this->addError($this->getBilletSettings()->validate($storeSettings));
            $this->addError($this->getPixSettings()->validate($storeSettings));
            $this->addError($this->getVoucherSettings()->validate($storeSettings));
            $this->addError($this->getDebitCardSettings()->validate($storeSettings));
        }

        return $this;
    }

    private function validateAccountEnabled()
    {
        if (!$this->isAccountEnabled()) {
            $this->addError(self::ACCOUNT_DISABLED);
        }
    }

    private function validateMultiBuyer()
    {
        $this->validateEnabledSetting('MultiBuyer', self::MULTIBUYERS_DISABLED);
    }

    private function validateMultiPayments()
    {
        $this->validateEnabledSetting('MultiPayments', self::MULTIPAYMENTS_DISABLED);
    }

    /**
     * @param StoreSettings|null $storeSettings
     * @return bool
     */
    private function canNotValidateUrlSetting($storeSettings = null)
    {
        return !$storeSettings || $storeSettings->isSandbox();
    }

    /**
     * @param mixed $setting
     * @param mixed $error
     * @return void
     */
    private function validateEnabledSetting($setting, $error)
    {
        $methodName = "is{$setting}Enabled";
        if (!$this->$methodName()) {
            $this->addError($error);
        }
    }

    /**
     * @param GetAccountResponse $accountInfo
     * @return Account
     */
    public static function createFromSdk($accountInfo)
    {
        $account = new Account();
        $orderSettings = $accountInfo->orderSettings;
        $account->setAccountEnabled($accountInfo->status);
        $account->setMultiPaymentsEnabled($orderSettings['multi_payments_enabled']);
        $account->setMultiBuyerEnabled($orderSettings['multi_buyers_enabled']);
        $account->setDomains($accountInfo->domains);
        $account->setWebhooks($accountInfo->webhookSettings);
        $account->setCreditCardSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::CREDIT_CARD));
        $account->setBilletSettings(
            PaymentMethodSettings::createFromSdk(
                $accountInfo,
                PaymentEnum::BILLET,
                PaymentEnum::BILLET_ACCOUNT
            )
        );
        $account->setPixSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::PIX));
        $account->setVoucherSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::VOUCHER));
        $account->setDebitCardSettings(PaymentMethodSettings::createFromSdk($accountInfo, PaymentEnum::DEBIT_CARD));

        return $account;
    }
}
