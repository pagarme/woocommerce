<?php

namespace Pagarme\Core\Kernel\Aggregates;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;
use Pagarme\Core\Kernel\Helper\StringFunctionsHelper;
use Pagarme\Core\Kernel\ValueObjects\AbstractValidString;
use Pagarme\Core\Kernel\ValueObjects\Configuration\AddressAttributes;
use Pagarme\Core\Kernel\ValueObjects\Configuration\CardConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\MarketplaceConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\PixConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\RecurrenceConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\VoucherConfig;
use Pagarme\Core\Kernel\ValueObjects\Configuration\DebitConfig;
use Pagarme\Core\Kernel\ValueObjects\Key\AbstractSecretKey;
use Pagarme\Core\Kernel\ValueObjects\Key\AbstractPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Key\TestPublicKey;
use Pagarme\Core\Kernel\ValueObjects\Id\GUID;

final class Configuration extends AbstractEntity
{
    const KEY_SECRET = 'KEY_SECRET';
    const KEY_PUBLIC = 'KEY_PUBLIC';

    const CARD_OPERATION_AUTH_ONLY = 'auth_only';
    const CARD_OPERATION_AUTH_AND_CAPTURE = 'auth_and_capture';

    /**
     *
     * @var bool
     */
    private $enabled;
    /**
     *
     * @var bool
     */
    private $boletoEnabled;
    /**
     *
     * @var bool
     */
    private $creditCardEnabled;
    /**
     *
     * @var bool
     */
    private $twoCreditCardsEnabled;
    /**
     *
     * @var bool
     */
    private $boletoCreditCardEnabled;
    /**
     *
     * @var bool
     */
    private $testMode;
    /**
     *
     * @var GUID
     */
    private $hubInstallId;

    /**
     *
     * @var string
     */
    private $hubEnvironment;

    /** @var string */
    private $cardOperation;

    /**
     *
     * @var AbstractValidString[]
     */
    private $keys;

    /**
     *
     * @var CardConfig[]
     */
    private $cardConfigs;


    /**
     * @var bool
     */
    private $antifraudEnabled;

    /**
     * @var int
     */
    private $antifraudMinAmount;

    /** @var bool */
    private $installmentsEnabled;

    /** @var AddressAttributes */
    private $addressAttributes;

    /** @var bool */
    private $allowNoAddress;

    /** @var string */
    private $cardStatementDescriptor;

    /** @var string */
    private $boletoInstructions;

    /** @var string */
    private $storeId;

    /** @var Configuration */
    private $parentConfiguration;

    /** @var array */
    private $methodsInherited;

    /** @var bool */
    private $inheritAll;

    /** @var bool */
    private $saveCards;

    /** @var bool */
    private $saveVoucherCards;

    /** @var bool */
    private $multiBuyer;

    /** @var RecurrenceConfig */
    private $recurrenceConfig;

    /** @var bool */
    private $installmentsDefaultConfig;

    /** @var int */
    private $boletoDueDays;

    /** @var string */
    private $boletoBankCode;

    /**
     * @var bool
     */
    private $sendMailEnabled;

    /**
     * @var bool
     */
    private $createOrderEnabled;

    /** @var VoucherConfig */
    private $voucherConfig;

    /** @var DebitConfig */
    private $debitConfig;

    /**
     * @var PixConfig
     */
    private $pixConfig;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $accountId;

    /**
     * @var MarketplaceConfig
     */
    private $marketplaceConfig;

    public function __construct()
    {
        $this->allowNoAddress = false;
        $this->saveCards = false;
        $this->saveVoucherCards = false;
        $this->multiBuyer = false;
        $this->cardConfigs = [];
        $this->methodsInherited = [];

        $this->keys = [
            self::KEY_SECRET => null,
            self::KEY_PUBLIC => null,
        ];

        $this->testMode = true;
        $this->inheritAll = false;
        $this->installmentsDefaultConfig = false;
    }

    /**
     * @return RecurrenceConfig
     */
    public function getRecurrenceConfig()
    {
        return $this->recurrenceConfig;
    }

    /**
     * @param RecurrenceConfig $recurrenceConfig
     */
    public function setRecurrenceConfig(RecurrenceConfig $recurrenceConfig)
    {
        $this->recurrenceConfig = $recurrenceConfig;
    }

    /**
     * @return DebitConfig
     */
    public function getDebitConfig()
    {
        return $this->debitConfig;
    }

    /**
     * @param DebitConfig $debitConfig
     */
    public function setDebitConfig(DebitConfig $debitConfig)
    {
        $this->debitConfig = $debitConfig;
    }

    /**
     * @param PixConfig $pixConfig
     */
    public function setPixConfig(PixConfig $pixConfig)
    {
        $this->pixConfig = $pixConfig;
    }

    /**
     * @return PixConfig
     */
    public function getPixConfig()
    {
        return $this->pixConfig;
    }

    /**
     * @param MarketplaceConfig $marketplaceConfig
     */
    public function setMarketplaceConfig(MarketplaceConfig $marketplaceConfig)
    {
        $this->marketplaceConfig = $marketplaceConfig;
    }

    /**
     * @return MarketplaceConfig
     */
    public function getMarketplaceConfig()
    {
        return $this->marketplaceConfig;
    }

    /**
     * @return VoucherConfig
     */
    public function getVoucherConfig()
    {
        return $this->voucherConfig;
    }

    /**
     * @param VoucherConfig $voucherConfig
     */
    public function setVoucherConfig(VoucherConfig $voucherConfig)
    {
        $this->voucherConfig = $voucherConfig;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    protected function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = filter_var(
            $enabled,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    protected function getPublicKey()
    {
        return $this->keys[self::KEY_PUBLIC];
    }

    protected function getSecretKey()
    {
        return $this->keys[self::KEY_SECRET];
    }

    /**
     *
     * @param string|array $key
     * @return $this
     */
    public function setPublicKey(AbstractPublicKey $key)
    {
        $this->testMode = false;

        $this->keys[self::KEY_PUBLIC] = $key;

        if (is_a($key, TestPublicKey::class)) {
            $this->testMode = true;
        };

        return $this;
    }

    /**
     *
     * @param string|array $key
     * @return $this
     */
    public function setSecretKey(AbstractSecretKey $key)
    {
        $this->keys[self::KEY_SECRET] = $key;
        return $this;
    }

    /**
     *
     * @return bool
     */
    protected function isTestMode()
    {
        return $this->testMode;
    }

    /**
     *
     * @return bool
     */
    public function isHubEnabled()
    {
        if ($this->getHubInstallId() && $this->getHubInstallId() instanceof GUID) {
            return
                $this->getHubInstallId()->getValue() !== null &&
                $this->getHubInstallId()->getValue() !== "00000000-0000-0000-0000-000000000000";
        }
        return false;
    }

    public function setHubInstallId(GUID $hubInstallId)
    {
        $this->hubInstallId = $hubInstallId;
    }

    public function getHubInstallId()
    {
        return $this->hubInstallId;
    }

    public function setHubEnvironment($hubEnvironment)
    {
        $this->hubEnvironment = $hubEnvironment;
    }

    public function getHubEnvironment()
    {
        return $this->hubEnvironment;
    }

    /**
     *
     * @param bool $boletoEnabled
     * @return Configuration
     */
    public function setBoletoEnabled($boletoEnabled)
    {
        $this->boletoEnabled = filter_var(
            $boletoEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param bool $creditCardEnabled
     * @return Configuration
     */
    public function setCreditCardEnabled($creditCardEnabled)
    {
        $this->creditCardEnabled = filter_var(
            $creditCardEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     * @param $sendMailEnable
     * @return $this
     */
    public function setSendMailEnabled($sendMailEnable)
    {
        $this->sendMailEnabled = filter_var(
            $sendMailEnable,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     * @param $createOrderEnabled
     * @return $this
     */
    public function setCreateOrderEnabled($createOrderEnabled)
    {
        $this->createOrderEnabled = filter_var(
            $createOrderEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param bool $twoCreditCardsEnabled
     * @return Configuration
     */
    public function setTwoCreditCardsEnabled($twoCreditCardsEnabled)
    {
        $this->twoCreditCardsEnabled = filter_var(
            $twoCreditCardsEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @param bool $boletoCreditCardEnabled
     * @return Configuration
     */
    public function setBoletoCreditCardEnabled($boletoCreditCardEnabled)
    {
        $this->boletoCreditCardEnabled = filter_var(
            $boletoCreditCardEnabled,
            FILTER_VALIDATE_BOOLEAN
        );
        return $this;
    }

    /**
     *
     * @return bool
     */
    protected function isBoletoEnabled()
    {
        return $this->boletoEnabled;
    }

    /**
     *
     * @return bool
     */
    protected function isCreditCardEnabled()
    {
        return $this->creditCardEnabled;
    }

    /**
     * @return bool
     */
    public function isSendMailEnabled()
    {
        return $this->sendMailEnabled;
    }

    /**
     * @return bool
     */
    protected function isCreateOrderEnabled()
    {
        return $this->createOrderEnabled;
    }

    /**
     *
     * @return bool
     */
    protected function isTwoCreditCardsEnabled()
    {
        return $this->twoCreditCardsEnabled;
    }

    /**
     *
     * @return bool
     */
    protected function isBoletoCreditCardEnabled()
    {
        return $this->boletoCreditCardEnabled;
    }

    /**
     *
     * @param CardConfig $newCardConfig
     * @throws InvalidParamException
     */
    public function addCardConfig(CardConfig $newCardConfig)
    {
        $cardConfigs = $this->getCardConfigs();
        foreach ($cardConfigs as $cardConfig) {
            if ($cardConfig->equals($newCardConfig)) {
                throw new InvalidParamException(
                    "The card config is already added!",
                    $newCardConfig->getBrand()
                );
            }
        }

        $this->cardConfigs[] = $newCardConfig;
    }

    /**
     *
     * @return CardConfig[]
     */
    protected function getCardConfigs()
    {
        return $this->cardConfigs !== null ? $this->cardConfigs : [];
    }

    /**
     * @return string
     */
    protected function getCardOperation()
    {
        return $this->cardOperation;
    }

    /**
     * @param string $cardOperation
     */
    public function setCardOperation($cardOperation)
    {
        $this->cardOperation = $cardOperation;
    }

    /**
     * @return bool
     */
    protected function isCapture()
    {
        return $this->getCardOperation() === self::CARD_OPERATION_AUTH_AND_CAPTURE;
    }

    /**
     * @return bool
     */
    protected function isAntifraudEnabled()
    {
        return $this->antifraudEnabled;
    }

    /**
     * @param bool $antifraudEnabled
     */
    public function setAntifraudEnabled($antifraudEnabled)
    {
        $this->antifraudEnabled = $antifraudEnabled;
    }

    /**
     * @return int
     */
    protected function getAntifraudMinAmount()
    {
        return $this->antifraudMinAmount;
    }

    /**
     * @param int $antifraudMinAmount
     * @throws InvalidParamException
     */
    public function setAntifraudMinAmount($antifraudMinAmount)
    {
        $numbers = '/([^0-9])/i';
        $replace = '';

        $minAmount = preg_replace($numbers, $replace, $antifraudMinAmount ?? '');

        if ($minAmount < 0) {
            $minAmount = 0;
        }
        $this->antifraudMinAmount = $minAmount;
    }

    /**
     * @return bool
     */
    protected function isInstallmentsEnabled()
    {
        return $this->installmentsEnabled;
    }

    /**
     * @param bool $installmentsEnabled
     */
    public function setInstallmentsEnabled($installmentsEnabled)
    {
        $this->installmentsEnabled = $installmentsEnabled;
    }

    /**
     * @return AddressAttributes
     */
    protected function getAddressAttributes()
    {
        return $this->addressAttributes;
    }

    /**
     * @return bool
     */
    protected function getAllowNoAddress()
    {
        return $this->allowNoAddress;
    }

    /**
     * @param bool $allowNoAddress
     */
    public function setAllowNoAddress($allowNoAddress)
    {
        $this->allowNoAddress = $allowNoAddress;
    }

    /**
     * @param AddressAttributes $addressAttributes
     */
    public function setAddressAttributes(AddressAttributes $addressAttributes)
    {
        $this->addressAttributes = $addressAttributes;
    }

    /**
     * @return string
     */
    protected function getCardStatementDescriptor()
    {
        return $this->cardStatementDescriptor;
    }

    /**
     * @param string $cardStatementDescriptor
     * @throws InvalidParamException
     */
    public function setCardStatementDescriptor($cardStatementDescriptor)
    {
        $stringFunctions = new StringFunctionsHelper();
        $value = $stringFunctions->removeSpecialCharacters($cardStatementDescriptor);

        if (strlen($value) > 22) {
            throw new InvalidParamException(
                'Invalid soft description',
                $value
            );
        }

        $this->cardStatementDescriptor = $value;
    }

    /**
     * @return string
     */
    public function getBoletoInstructions()
    {
        return $this->boletoInstructions;
    }

    /**
     * @param string $boletoInstructions
     */
    public function setBoletoInstructions($boletoInstructions)
    {
        $this->boletoInstructions = $boletoInstructions;
    }

    /**
     * @return bool
     */
    public function isSaveCards()
    {
        return $this->saveCards;
    }

    /**
     * @param bool $saveCards
     */
    public function setSaveCards($saveCards)
    {
        $this->saveCards = $saveCards;
    }

    /**
     * @return bool
     */
    public function isSaveVoucherCards()
    {
        return $this->saveVoucherCards;
    }

    /**
     * @param bool $saveVoucherCards
     */
    public function setSaveVoucherCards($saveVoucherCards)
    {
        $this->saveVoucherCards = $saveVoucherCards;
    }

    /**
     * @return bool
     */
    public function isMultiBuyer()
    {
        return $this->multiBuyer;
    }

    /**
     * @param bool $multiBuyer
     */
    public function setMultiBuyer($multiBuyer)
    {
        $this->multiBuyer = $multiBuyer;
    }

    /**
     * @return int
     */
    public function getBoletoDueDays()
    {
        return $this->boletoDueDays;
    }

    /**
     * @param int $boletoDueDays
     */
    public function setBoletoDueDays($boletoDueDays)
    {
        if (!is_numeric($boletoDueDays)) {
            throw new InvalidParamException("Boleto due days should be an integer!", $boletoDueDays);
        }

        $this->boletoDueDays = (int) $boletoDueDays;
    }

    /**
     * @return string
     */
    public function getBoletoBankCode()
    {
        return $this->boletoBankCode;
    }

    /**
     * @param string $boletoBankCode
     */
    public function setBoletoBankCode($boletoBankCode)
    {
        $this->boletoBankCode = $boletoBankCode;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link   https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since  5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "enabled" => $this->enabled,
            "antifraudEnabled" => $this->isAntifraudEnabled(),
            "antifraudMinAmount" => $this->getAntifraudMinAmount(),
            "boletoEnabled" => $this->boletoEnabled,
            "creditCardEnabled" => $this->creditCardEnabled,
            "saveCards" => $this->isSaveCards(),
            "saveVoucherCards" => $this->isSaveVoucherCards(),
            "multiBuyer" => $this->isMultiBuyer(),
            "twoCreditCardsEnabled" => $this->twoCreditCardsEnabled,
            "boletoCreditCardEnabled" => $this->boletoCreditCardEnabled,
            "testMode" => $this->testMode,
            "hubInstallId" => $this->isHubEnabled() ? $this->hubInstallId->getValue() : null,
            "hubEnvironment" => $this->hubEnvironment,
            "merchantId" => $this->getMerchantId(),
            "accountId" => $this->getAccountId(),
            "addressAttributes" => $this->getAddressAttributes(),
            "allowNoAddress" => $this->getAllowNoAddress(),
            "keys" => $this->keys,
            "cardOperation" => $this->cardOperation,
            "installmentsEnabled" => $this->isInstallmentsEnabled(),
            "installmentsDefaultConfig" => $this->isInstallmentsDefaultConfig(),
            "cardStatementDescriptor" => $this->getCardStatementDescriptor(),
            "boletoInstructions" => $this->getBoletoInstructions(),
            "boletoDueDays" => $this->getBoletoDueDays(),
            "boletoBankCode" => $this->getBoletoBankCode(),
            "cardConfigs" => $this->getCardConfigs(),
            "storeId" => $this->getStoreId(),
            "methodsInherited" => $this->getMethodsInherited(),
            "parentId" => $this->getParentId(),
            "parent" => $this->parentConfiguration,
            "inheritAll" => $this->isInheritedAll(),
            "recurrenceConfig" => $this->getRecurrenceConfig(),
            "sendMail" => $this->isSendMailEnabled(),
            "createOrder" => $this->isCreateOrderEnabled(),
            "voucherConfig" => $this->getVoucherConfig(),
            "debitConfig" => $this->getDebitConfig(),
            "pixConfig" => $this->getPixConfig(),
            "marketplaceConfig" => $this->getMarketplaceConfig()
        ];
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @param Configuration $parentConfiguration
     */
    public function setParentConfiguration(Configuration $parentConfiguration)
    {
        $this->parentConfiguration = $parentConfiguration;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        if ($this->parentConfiguration === null) {
            return null;
        }
        return $this->parentConfiguration->getId();
    }

    /**
     * @param array $methods
     */
    public function setMethodsInherited($methods)
    {
        $this->methodsInherited = $methods;
    }

    /**
     * @return array
     */
    public function getMethodsInherited()
    {
        if ($this->parentConfiguration === null) {
            return [];
        }
        return $this->methodsInherited;
    }

    /**
     * @return bool
     */
    public function isInheritedAll()
    {
        if ($this->parentConfiguration === null) {
            return false;
        }

        return $this->inheritAll;
    }

    /**
     * @param bool $inheritAll
     */
    public function setInheritAll($inheritAll)
    {
        $this->inheritAll = $inheritAll;
    }

    /**
     * @return bool
     */
    public function isInstallmentsDefaultConfig()
    {
        return $this->installmentsDefaultConfig;
    }

    /**
     * @param bool $installmentsDefaultConfig
     * @return Configuration
     */
    public function setInstallmentsDefaultConfig($installmentsDefaultConfig)
    {
        $this->installmentsDefaultConfig = $installmentsDefaultConfig;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $methodSplited = explode(
            "_",
            preg_replace('/(?<=\\w)(?=[A-Z])/',"_$1", $method ?? '')
        );

        $targetObject = $this;

        $actions = ['is', 'get'];
        $useDefault = in_array($method, $targetObject->getMethodsInherited());

        if ($this->isMethodsIgnoringFather($method, $methodSplited, $actions, $targetObject)) {
            return call_user_func([$targetObject, $method], $arguments);
        }

        if ((in_array($methodSplited[0], $actions) && $useDefault) || $this->isInheritedAll()) {
            if ($this->parentConfiguration !== null) {
                $targetObject = $this->parentConfiguration;
            }
        }

        return call_user_func([$targetObject, $method], $arguments);
    }

    private function isMethodsIgnoringFather($method, $methodSplited, $actions, $targetObject)
    {
        $methodsIgnoringFather = ["getSecretKey", "getPublicKey", "isHubEnabled"];

        if (
            in_array($method, $methodsIgnoringFather) &&
            (in_array($methodSplited[0], $actions)) &&
            $targetObject->getHubInstallId() !== null
        ) {
            return true;
        }

        return false;
    }
}
