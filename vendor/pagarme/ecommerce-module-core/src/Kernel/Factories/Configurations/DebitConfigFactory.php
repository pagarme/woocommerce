<?php

namespace Pagarme\Core\Kernel\Factories\Configurations;

use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\DebitConfig;

class DebitConfigFactory implements FactoryCreateFromDbDataInterface
{
    /**
     * @param array $data
     * @return DebitConfig
     * @throws InvalidParamException
     */
    public function createFromDbData($data)
    {
        $debitConfig = new DebitConfig();

        if (isset($data->enabled)) {
            $debitConfig->setEnabled((bool) $data->enabled);
        }

        if (!empty($data->title)) {
            $debitConfig->setTitle($data->title);
        }

        if (!empty($data->cardOperation)) {
            $debitConfig->setCardOperation($data->cardOperation);
        }

        if (!empty($data->cardStatementDescriptor)) {
            $debitConfig->setCardStatementDescriptor(
                $data->cardStatementDescriptor
            );
        }

        if (isset($data->saveCards)) {
            $debitConfig->setSaveCards((bool) $data->saveCards);
        }

        foreach ($data->cardConfigs as $cardConfig) {
            $brand = strtolower($cardConfig->brand);
            $debitConfig->addCardConfig(
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

        return $debitConfig;
    }
}
