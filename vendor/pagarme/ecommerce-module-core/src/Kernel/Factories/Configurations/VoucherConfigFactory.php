<?php

namespace Pagarme\Core\Kernel\Factories\Configurations;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\VoucherConfig;

class VoucherConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param array $data
     * @return VoucherConfig
     * @throws InvalidParamException
     */
    public function createFromDbData($data)
    {
        $voucherConfig = new VoucherConfig();

        if (isset($data->enabled)) {
            $voucherConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $voucherConfig->setTitle($data->title);
        }

        if (!empty($data->cardOperation)) {
            $voucherConfig->setCardOperation($data->cardOperation);
        }

        if (!empty($data->cardStatementDescriptor)) {
            $voucherConfig->setCardStatementDescriptor(
                $data->cardStatementDescriptor
            );
        }

        if (isset($data->saveCards)) {
            $voucherConfig->setSaveCards((bool) $data->saveCards);
        }

        if (isset($data->saveVoucherCards)) {
            $voucherConfig->setSaveVoucherCards((bool) $data->saveVoucherCards);
        }

        if (isset($data->cardConfigs)) {
            foreach ($data->cardConfigs as $cardConfig) {
                $brand = strtolower($cardConfig->brand);
                $voucherConfig->addCardConfig(
                    new CardConfig(
                        $cardConfig->enabled,
                        CardBrand::$brand(),
                        $cardConfig->maxInstallment,
                        $cardConfig->maxInstallmentWithoutInterest,
                        $cardConfig->initialInterest,
                        $cardConfig->incrementalInterest,
                        $cardConfig->minValue
                    )
                );
            }
        }

        return $voucherConfig;
    }
}
