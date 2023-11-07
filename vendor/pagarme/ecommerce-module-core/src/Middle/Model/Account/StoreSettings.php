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

    /**
     * @return bool
     */
    public function isSandbox()
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     * @return void
     */
    public function setSandbox(bool $sandbox)
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return array
     */
    public function getEnabledPaymentMethods()
    {
        return $this->enabledPaymentMethods;
    }

    /**
     * @param string $paymentMethodName
     * @return bool
     */
    public function isPaymentMethodEnabled($paymentMethodName)
    {
        return (bool) isset($this->enabledPaymentMethods[$paymentMethodName])
            ? $this->enabledPaymentMethods[$paymentMethodName]
            : false;
    }

    /**
     * @param array $enabledPaymentMethods
     * @return void
     */
    public function setEnabledPaymentMethods($enabledPaymentMethods)
    {
        $this->enabledPaymentMethods = $enabledPaymentMethods;
    }

    /**
     * @return array
     */
    public function getStoreUrls()
    {
        return $this->storeUrls;
    }

    /**
     * @param array $storeUrls
     * @return void
     */
    public function setStoreUrls($storeUrls)
    {
        $this->storeUrls = $storeUrls;
    }


}
