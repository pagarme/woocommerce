<?php

namespace Pagarme\Core\Payment\Services;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Order;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\TransactionType;
use Pagarme\Core\Payment\Factories\SavedCardFactory;
use Pagarme\Core\Payment\Repositories\SavedCardRepository;

class CardService
{
    private $logService;

    public function __construct()
    {
        $this->logService = $this->getLogService();
    }

    public function getBrandsAvailables(AbstractEntity $config)
    {
        $brandsAvailables = [];
        $cardConfigs = $config->getCardConfigs();

        foreach ($cardConfigs as $cardConfig) {
            if (
                $cardConfig->isEnabled() &&
                !$cardConfig->getBrand()->equals(CardBrand::nobrand())
            ) {
                $brandsAvailables[] = $cardConfig->getBrand()->getName();
            }
        }

        return $brandsAvailables;
    }

    public function saveCards(Order $order)
    {
        $savedCardFactory = new SavedCardFactory();
        $savedCardRepository = new SavedCardRepository();
        $charges = $order->getCharges();

        foreach ($charges as $charge) {
            $lastTransaction = $charge->getLastTransaction();
            if ($lastTransaction === null) {
                continue;
            }

            if (
            !(
                $lastTransaction->getTransactionType()->equals(TransactionType::creditCard()) ||
                $lastTransaction->getTransactionType()->equals(TransactionType::voucher()) ||
                $lastTransaction->getTransactionType()->equals(TransactionType::debitCard())
            )
            ) {
                continue; //save only credit card transactions;
            }

            $metadata = $charge->getMetadata();
            $saveOnSuccess =
                isset($metadata->saveOnSuccess) &&
                $metadata->saveOnSuccess === "true";

            if (
                !empty($lastTransaction->getCardData()) &&
                $saveOnSuccess &&
                $order->getCustomer()->getPagarmeId()->equals(
                    $charge->getCustomer()->getPagarmeId()
                )
            ) {
                $postData =
                    json_decode(json_encode($lastTransaction->getCardData()));
                $postData->owner =
                    $charge->getCustomer()->getPagarmeId();
                if( !property_exists($postData, "type") ) {
                    $postData->type = $lastTransaction->getTransactionType()->getType();
                }
                $savedCard = $savedCardFactory->createFromTransactionJson($postData);
                if (
                    $savedCardRepository->findByPagarmeId($savedCard->getPagarmeId()) === null
                ) {
                    $savedCardRepository->save($savedCard);
                    $this->logService->info(
                        "Card '{$savedCard->getPagarmeId()->getValue()}' saved."
                    );
                }
            }
        }
    }

    public function getLogService()
    {
        return new LogService("Card Service", true);
    }
}
