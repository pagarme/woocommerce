<?php

namespace Pagarme\Core\Kernel\Factories\Configurations;

use Pagarme\Core\Kernel\Interfaces\FactoryCreateFromDbDataInterface;
use Pagarme\Core\Kernel\ValueObjects\Configuration\MarketplaceConfig;

class MarketplaceConfigFactory implements FactoryCreateFromDbDataInterface
{

    /**
     * @param object $data
     * @return MarketplaceConfig
     */
    public function createFromDbData($data)
    {
        $marketplaceConfig = new MarketplaceConfig();

        if (isset($data->enabled)) {
            $marketplaceConfig->setEnabled(
                (bool) $data->enabled
            );
        }

        if (isset($data->responsibilityForProcessingFees)) {
            $marketplaceConfig->setResponsibilityForProcessingFees(
                $data->responsibilityForProcessingFees
            );
        }

        if (isset($data->responsibilityForChargebacks)) {
            $marketplaceConfig->setResponsibilityForChargebacks(
                $data->responsibilityForChargebacks
            );
        }

        if (isset($data->responsibilityForReceivingSplitRemainder)) {
            $marketplaceConfig->setResponsibilityForReceivingSplitRemainder(
                $data->responsibilityForReceivingSplitRemainder
            );
        }

        if (isset($data->responsibilityForReceivingExtrasAndDiscounts)) {
            $marketplaceConfig->setResponsibilityForReceivingExtrasAndDiscounts(
                $data->responsibilityForReceivingExtrasAndDiscounts
            );
        }

        if (isset($data->mainRecipientId)) {
            $marketplaceConfig->setMainRecipientId(
                $data->mainRecipientId
            );
        }

        return $marketplaceConfig;
    }
}
