<?php

namespace Pagarme\Core\Kernel\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Installment;

final class InstallmentService
{
    const MAX_PSP_INSTALLMENTS_NUMBER = 18;
    const MAX_GATEWAY_INSTALLMENTS_NUMBER = 24;
    const INSTALLMENT_OVER_ISSUER_LIMIT_ERROR_MESSAGE_PTBR =
        'Banco emissor não aceita o parcelamento selecionado. Por favor, selecione uma opção de parcelamento menor.';

    /**
     *
     * @param  Order|null     $order
     * @param  CardBrand|null $brand
     * @param  null           $value
     * @return Installment[]
     */
    public function getInstallmentsFor(
        Order $order = null,
        CardBrand $brand = null,
        $value = null,
        $config = null
    ) {
        $amount = 0;
        if($order !== null) {
            $platformOrder = $order->getPlatformOrder();
            $amount = $platformOrder->getGrandTotal() * 100;
        }

        if ($value !== null) {
            $amount = $value;
        }

        if ($config == null) {
            $config = MPSetup::getModuleConfiguration();
        }

        $installmentsEnabled = false;
        if (
            method_exists($config, 'isInstallmentsEnabled') &&
            $config->isInstallmentsEnabled()
        ) {
            $installmentsEnabled = true;
        }

        $useDefaultInstallmentsConfig = $this->getUseDefaultInstallments($config);

        $baseBrand = CardBrand::nobrand();
        if ($brand !== null && !$useDefaultInstallmentsConfig) {
            $baseBrand = $brand;
        }

        $cardConfigs = $config->getCardConfigs();

        $brandConfig = null;

        foreach ($cardConfigs as $cardConfig) {
            if ($cardConfig->getBrand()->equals($baseBrand)) {
                $brandConfig = $cardConfig;
                break;
            }
        }

        if ($brandConfig === null) {
            return [];
        }

        $installments = [];
        for (
            $i = 1;
            $i <= $brandConfig->getMaxInstallmentWithoutInterest();
            $i++
        ) {
            $installments[] = new Installment($i, $amount, 0);
        }

        if (!$installmentsEnabled) {
            return array_slice($installments, 0, 1);
        }

        for (
            $i = $brandConfig->getMaxInstallmentWithoutInterest() + 1,
            $interestCicle = 0;
            $i <= $brandConfig->getMaxInstallment();
            $i++,
            $interestCicle++
        ) {
            $interest = $brandConfig->getInitialInterest();
            $interest += $brandConfig->getIncrementalInterest() * $interestCicle;
            $installments[] = new Installment($i, $amount, $interest / 100);
        }

        return $this->filterInstallmentsByMinValue($installments, $brandConfig);
    }

    public function getUseDefaultInstallments($config)
    {
        if ($config == null || $config instanceof AbstractEntity) {
            return MPSetup::getModuleConfiguration()->isInstallmentsDefaultConfig();
        }
        return false;
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public static function isInstallmentErrorMessage($message)
    {
        return strpos($message, self::INSTALLMENT_OVER_ISSUER_LIMIT_ERROR_MESSAGE_PTBR) >= 0;
    }

    public function getLabelFor(Installment $installment)
    {
        $i18n = new LocalizationService();

        $interestLabel = $i18n->getDashboard('without interest');
        if ($installment->getInterest() > 0) {
            $interestLabel = $i18n->getDashboard('with interest');
        }

        $moneyService = new MoneyService();

        $formattedValue = MPSetup::formatToCurrency(
            $moneyService->centsToFloat((int) $installment->getValue())
        );

        $formattedTotal = MPSetup::formatToCurrency(
            $moneyService->centsToFloat((int) $installment->getTotal())
        );

        $label = $i18n->getDashboard(
            "%dx of %s %s (Total: %s)",
            $installment->getTimes(),
            $formattedValue,
            $interestLabel,
            $formattedTotal
        );

        return $label;
    }

    /**
     *
     * @param  Installment[] $installments
     * @param  CardConfig    $brandConfig
     * @return array
     */
    protected function filterInstallmentsByMinValue(array $installments, CardConfig $brandConfig)
    {
        return array_filter(
            $installments,
            function (Installment $installment) use ($brandConfig) {
                return
                    $installment->getTimes() === 1 ||
                    $installment->getValue() >= $brandConfig->getMinValue();
            }
        );
    }
}
