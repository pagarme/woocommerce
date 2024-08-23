<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class GooglePayConfig extends AbstractValueObject
{
    private $enabled;
    private $title;
    private $merchantId;
    private $merchantName;

    /**
     * @param bool $enabled
     * @return GooglePayConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param string $title
     * @return GooglePayConfig
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @param string $merchantId
     * @return GooglePayConfig
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * @param string $merchantId
     * @return GooglePayConfig
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;
        return $this;
    }

    protected function isEqual($object)
    {
        return $this->enabled === $this->isEnabled()
            && $this->title === $this->getTitle()
            && $this->merchantName === $this->getMerchantName()
            && $this->merchantId === $this->getMerchantId();
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getMerchantName()
    {
        return $this->merchantName;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "enabled" => $this->isEnabled(),
            "title" => $this->getTitle(),
            "merchantId" => $this->getMerchantId(),
            "merchantName" => $this->getMerchantName(),
        ];
    }
}
