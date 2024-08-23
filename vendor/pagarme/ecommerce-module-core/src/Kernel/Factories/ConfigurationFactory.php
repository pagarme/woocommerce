<?php

namespace Pagarme\Core\Kernel\Factories;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Aggregates\Configuration;
use Pagarme\Core\Kernel\Factories\Configurations\DebitConfigFactory;
use Pagarme\Core\Kernel\Factories\Configurations\MarketplaceConfigFactory;
use Pagarme\Core\Kernel\Factories\Configurations\PixConfigFactory;
use Pagarme\Core\Kernel\Factories\Configurations\GooglePayConfigFactory;
use Pagarme\Core\Kernel\Factories\Configurations\RecurrenceConfigFactory;
use Pagarme\Core\Kernel\Factories\Configurations\VoucherConfigFactory;
use Pagarme\Core\Kernel\Interfaces\FactoryInterface;
use Pagarme\Core\Kernel\Repositories\ConfigurationRepository;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;
use Pagarme\Core\Kernel\ValueObjects\Key\HubAccessTokenKey;
use Pagarme\Core\Kernel\ValueObjects\Key\PublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\SecretKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestSecretKey;
use Exception;

class ConfigurationFactory implements FactoryInterface
{
    public function createEmpty()
    {
        return new Configuration();
    }

    public function createFromPostData($postData)
    {
        $config = new Configuration();

        foreach ($postData['creditCard'] as $brand => $cardConfig) {
            $config->addCardConfig(
                new CardConfig(
                    $cardConfig['is_enabled'],
                    $brand,
                    $cardConfig['installments_up_to'],
                    $cardConfig['installments_without_interest'],
                    $cardConfig['interest'],
                    $cardConfig['incremental_interest'],
                    null
                )
            );
        }

        $config->setBoletoEnabled($postData['payment_pagarme_boleto_status']);
        $config->setCreditCardEnabled($postData['payment_pagarme_credit_card_status']);
        $config->setBoletoCreditCardEnabled($postData['payment_pagarme_boletoCreditCard_status']);
        $config->setTwoCreditCardsEnabled($postData['payment_pagarme_credit_card_two_credit_cards_enabled']);

        $config->setStoreId($postData['payment_pagarme_store_id']);

        return $config;
    }

    public function createFromJsonData($json)
    {
        $config = new Configuration();
        $data = json_decode($json);

        $this->createCardConfigs($data, $config);

        $antifraudEnabled = false;
        $antifraudMinAmount = 0;

        if (!empty($data->antifraudEnabled)) {
            $antifraudEnabled = $data->antifraudEnabled;
            $antifraudMinAmount = $data->antifraudMinAmount;
        }

        $config->setAntifraudEnabled($antifraudEnabled);
        if (isset($data->saveVoucherCards)) {
            $config->setSaveVoucherCards($data->saveVoucherCards);
        }
        $config->setAntifraudMinAmount($antifraudMinAmount);
        $config->setBoletoEnabled($data->boletoEnabled);
        $config->setCreditCardEnabled($data->creditCardEnabled);
        $config->setBoletoCreditCardEnabled($data->boletoCreditCardEnabled);
        $config->setTwoCreditCardsEnabled($data->twoCreditCardsEnabled);

        if (empty($data->createOrder)) {
            $data->createOrder = false;
        }
        $config->setCreateOrderEnabled($data->createOrder);

        if (!empty($data->merchantId)) {
            $config->setMerchantId($data->merchantId);
        }

        if (!empty($data->accountId)) {
            $config->setAccountId($data->accountId);
        }

        if (!empty($data->sendMail)) {
            $config->setSendMailEnabled($data->sendMail);
        }

        if (!empty($data->methodsInherited)) {
            $config->setMethodsInherited($data->methodsInherited);
        }

        if (!empty($data->inheritAll)) {
            $config->setInheritAll($data->inheritAll);
        }

        if (!empty($data->storeId) && $data->storeId !== null) {
            $config->setStoreId($data->storeId);
        }

        if (!empty($data->parentId)) {
            $configurationRepository = new ConfigurationRepository();
            $configDefault = $configurationRepository->find($data->parentId);
            $config->setParentConfiguration($configDefault);
        }

        $isInstallmentsEnabled = false;
        if (!empty($data->installmentsEnabled)) {
            $isInstallmentsEnabled = $data->installmentsEnabled;
        }
        $config->setInstallmentsEnabled($isInstallmentsEnabled);

        if (!empty($data->enabled)) {
            $config->setEnabled($data->enabled);
        }

        if (!empty($data->cardOperation)) {
            $config->setCardOperation($data->cardOperation);
        }

        if ($data->hubInstallId !== null) {
            $config->setHubInstallId(
                new GUID($data->hubInstallId)
            );
        }

        if (!empty($data->hubEnvironment)) {
            $config->setHubEnvironment($data->hubEnvironment);
        }

        if (!empty($data->keys)) {
            if (!isset($data->publicKey)) {
                $index = Configuration::KEY_PUBLIC;
                $data->publicKey = $data->keys->$index;
            }

            if (!isset($data->secretKey)) {
                $index = Configuration::KEY_SECRET;
                $data->secretKey = $data->keys->$index;
            }
        }

        if (!empty($data->publicKey)) {
            $config->setPublicKey(
                $this->createPublicKey($data->publicKey)
            );
        }

        if (!empty($data->secretKey)) {
            $config->setSecretKey(
                $this->createSecretKey($data->secretKey)
            );
        }

        if (!empty($data->addressAttributes)) {
            $config->setAddressAttributes(
                new AddressAttributes(
                    $data->addressAttributes->street,
                    $data->addressAttributes->number,
                    $data->addressAttributes->neighborhood,
                    $data->addressAttributes->complement
                )
            );
        }

        if (!empty($data->cardStatementDescriptor)) {
            $config->setCardStatementDescriptor($data->cardStatementDescriptor);
        }

        if (!empty($data->boletoInstructions)) {
            $config->setBoletoInstructions($data->boletoInstructions);
        }

        if (!empty($data->boletoBankCode)) {
            $config->setBoletoBankCode($data->boletoBankCode);
        }
        if (!empty($data->boletoDueDays)) {
            $config->setBoletoDueDays((int)$data->boletoDueDays);
        }

        if (!empty($data->saveCards)) {
            $config->setSaveCards($data->saveCards);
        }

        if (!empty($data->multibuyer)) {
            $config->setMultiBuyer($data->multibuyer);
        }

        if (!empty($data->recurrenceConfig)) {
            $config->setRecurrenceConfig(
                (new RecurrenceConfigFactory())
                    ->createFromDbData($data->recurrenceConfig)
            );
        }

        if (isset($data->installmentsDefaultConfig)) {
            $config->setInstallmentsDefaultConfig(
                $data->installmentsDefaultConfig
            );
        }

        if (!empty($data->voucherConfig)) {
            $config->setVoucherConfig(
                (new VoucherConfigFactory)
                    ->createFromDbData($data->voucherConfig)
            );
        }

        if (!empty($data->debitConfig)) {
            $config->setDebitConfig(
                (new DebitConfigFactory)
                    ->createFromDbData($data->debitConfig)
            );
        }

        if (!empty($data->pixConfig)) {
            $config->setPixConfig(
                (new PixConfigFactory())->createFromDbData($data->pixConfig)
            );
        }
        if (!empty($data->googlePayConfig)) {
            $config->setGooglePayConfig(
                (new GooglePayConfigFactory())->createFromDbData($data->googlePayConfig)
            );
        }

        if (!empty($data->allowNoAddress)) {
            $config->setAllowNoAddress($data->allowNoAddress);
        }

        if (!empty($data->marketplaceConfig)) {
            $config->setMarketplaceConfig(
                (new MarketplaceConfigFactory())
                    ->createFromDbData($data->marketplaceConfig)
            );
        }

        return $config;
    }

    private function createCardConfigs($data, Configuration $config)
    {
        try {
            foreach ($data->cardConfigs as $cardConfig) {
                $brand = strtolower($cardConfig->brand);
                $config->addCardConfig(
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
        } catch (Exception $e) {

        }
    }

    private function createPublicKey($key)
    {
        try {
            return new TestPublicKey($key);
        } catch (\Exception $e) {

        } catch (\Throwable $e) {

        }

        return new PublicKey($key);
    }

    private function createSecretKey($key)
    {
        try {
            return new TestSecretKey($key);
        } catch (\Exception $e) {

        } catch (\Throwable $e) {

        }

        try {
            return new SecretKey($key);
        } catch (\Exception $e) {

        } catch (\Throwable $e) {

        }

        return new HubAccessTokenKey($key);
    }

    /**
     *
     * @param array $dbData
     * @return AbstractEntity
     */
    public function createFromDbData($dbData)
    {
        // TODO: Implement createFromDbData() method.
    }
}
