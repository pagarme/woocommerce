<?php

namespace Pagarme\Core\Middle\Model\Account;

use PagarmeCoreApiLib\Models\GetAccountResponse;

class PaymentMethodSettings
{
    const PIX_DISABLED = 'pixDisabled';

    const CREDITCARD_DISABLED = 'creditCardDisabled';

    const BILLET_DISABLED = 'billetDisabled';

    const VOUCHER_DISABLED = 'voucherDisabled';

    const DEBITCARD_DISABLED = 'debitDisabled';

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $gatewayType;

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return void
     */
    public function setEnabled($enabled)
    {

        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGatewayType()
    {
        return $this->gatewayType;
    }

    /**
     * @param string $gatewayType
     * @return void
     */
    public function setGatewayType($gatewayType)
    {
        $this->gatewayType = $gatewayType;
    }

    public function isGateway()
    {
        return $this->gatewayType === 'mundipagg';
    }

    public function isPSP()
    {
        return $this->gatewayType === 'pagarme';
    }

    public function validate(StoreSettings $storeSettings)
    {
        $storePaymentMethodEnabled = $storeSettings->isPaymentMethodEnabled($this->getName());
        if (!$this->isEnabled() && $storePaymentMethodEnabled) {
            $name = strtoupper($this->getName());
            $errorName = "self::{$name}_DISABLED";
            return constant($errorName);
        }
        return '';
    }

    public static function createFromSdk(
        GetAccountResponse $accountInfo,
        string $paymentMethodName,
        string $paymentMethodSettingKey = null
    ) {
        $paymentMethodSettingKey = $paymentMethodSettingKey ?? $paymentMethodName;
        $paymentMethodSettings = new PaymentMethodSettings();
        $paymentMethodSettingsProperty = "{$paymentMethodSettingKey}Settings";

        $paymentMethodSettings->setName($paymentMethodName);
        if (!property_exists($accountInfo, $paymentMethodSettingsProperty)) {
            $paymentMethodSettings->setEnabled(false);
            return $paymentMethodSettings;
        }
        $paymentSettings = $accountInfo->{$paymentMethodSettingsProperty} ?? [];
        $paymentMethodSettings->setEnabled($paymentSettings['enabled'] ?? false);
        $paymentMethodSettings->setGatewayType($paymentSettings['gateway'] ?? '');


        return $paymentMethodSettings;
    }
}
