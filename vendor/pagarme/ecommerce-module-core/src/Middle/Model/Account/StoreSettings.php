<?php

namespace Pagarme\Core\Middle\Model\Account;

class StoreSettings
{
    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @var array
     */
    private $enabledPaymentMethods;

    /**
     * @var array
     */
    private $storeUrls;

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function setSandbox(bool $sandbox): void
    {
        $this->sandbox = $sandbox;
    }

    public function getEnabledPaymentMethods(): array
    {
        return $this->enabledPaymentMethods;
    }

    /**
     * @param string $paymentMethodName
     * @return bool
     */
    public function isPaymentMethodEnabled(string $paymentMethodName)
    {
        return (bool) isset($this->enabledPaymentMethods[$paymentMethodName])
            ? $this->enabledPaymentMethods[$paymentMethodName]
            : false;
    }

    public function setEnabledPaymentMethods(array $enabledPaymentMethods): void
    {
        $this->enabledPaymentMethods = $enabledPaymentMethods;
    }

    public function getStoreUrls(): array
    {
        return $this->storeUrls;
    }

    public function setStoreUrls(array $storeUrls): void
    {
        $this->storeUrls = $storeUrls;
    }


}
