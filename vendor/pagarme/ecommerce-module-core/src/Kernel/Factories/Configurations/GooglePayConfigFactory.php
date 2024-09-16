<?php

namespace Pagarme\Core\Kernel\Factories\Configurations;

use Pagarme\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Pagarme\Core\Kernel\ValueObjects\Configuration\GooglePayConfig;

class GooglePayConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param object $data
     * @return GooglePayConfig
     */
    public function createFromDbData($data)
    {
        $googlePayConfig = new GooglePayConfig();

        if (isset($data->enabled)) {
            $googlePayConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $googlePayConfig->setTitle($data->title);
        }

        if (!empty($data->merchantId)) {
            $googlePayConfig->setMerchantId($data->merchantId);
        }
        if (!empty($data->merchantName)) {
            $googlePayConfig->setMerchantName($data->merchantName);
        }

        return $googlePayConfig;
    }
}
