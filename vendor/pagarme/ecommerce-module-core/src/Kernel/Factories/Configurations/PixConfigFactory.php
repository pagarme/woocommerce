<?php

namespace Pagarme\Core\Kernel\Factories\Configurations;

use Pagarme\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Pagarme\Core\Kernel\ValueObjects\Configuration\PixConfig;

class PixConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param object $data
     * @return PixConfig
     */
    public function createFromDbData($data)
    {
        $pixConfig = new PixConfig();

        if (isset($data->enabled)) {
            $pixConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $pixConfig->setTitle($data->title);
        }

        if (!empty($data->expirationQrCode)) {
            $pixConfig->setExpirationQrCode($data->expirationQrCode);
        }

        if (!empty($data->additionalInformation)) {
            $additionalInformationArray = json_decode(
                json_encode($data->additionalInformation),
                true
            );

            $pixConfig->setAdditionalInformation(
                $additionalInformationArray
            );
        }

        if (!empty($data->bankType)) {
            $pixConfig->setBankType(
                $data->bankType
            );
        }

        return $pixConfig;
    }
}
