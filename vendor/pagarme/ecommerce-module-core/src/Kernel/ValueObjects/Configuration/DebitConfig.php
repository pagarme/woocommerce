<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

class DebitConfig extends AbstractValueObject
{
    const CARD_OPERATION_AUTH_ONLY = 'auth_only';
    const CARD_OPERATION_AUTH_AND_CAPTURE = 'auth_and_capture';

    /** @var bool */
    private $enabled;

    /** @var string */
    private $title;

    /** @var string */
    private $cardOperation = self::CARD_OPERATION_AUTH_AND_CAPTURE;

    /** @var string */
    private $cardStatementDescriptor;

    /** @var bool */
    private $saveCards;

    /** @var CardConfig[] */
    private $cardConfigs;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return DebitConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardOperation()
    {
        return $this->cardOperation;
    }

    /**
     * @param string $cardOperation
     * @return DebitConfig
     */
    public function setCardOperation($cardOperation)
    {
        $this->cardOperation = $cardOperation;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardStatementDescriptor()
    {
        return $this->cardStatementDescriptor;
    }

    /**
     * @param string $cardStatementDescriptor
     * @return DebitConfig
     */
    public function setCardStatementDescriptor($cardStatementDescriptor)
    {
        $this->cardStatementDescriptor = $cardStatementDescriptor;
        return $this;
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
     * @return DebitConfig
     */
    public function setSaveCards($saveCards)
    {
        $this->saveCards = $saveCards;
        return $this;
    }

    /**
     *
     * @param CardConfig $newCardConfig
     * @return DebitConfig
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
        return $this;
    }

    /**
     *
     * @return CardConfig[]
     */
    public function getCardConfigs()
    {
        return $this->cardConfigs !== null ? $this->cardConfigs : [];
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return DebitConfig
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function isCapture()
    {
        return $this->getCardOperation() === self::CARD_OPERATION_AUTH_AND_CAPTURE;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->enabled === $object->isEnabled() &&
            $this->cardOperation === $object->getCardOperation() &&
            $this->cardStatementDescriptor === $object->getCardStatementDescriptor() &&
            $this->saveCards === $object->isSaveCards;
    }

    /**
      * Specify data which should be serialized to JSON
      * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
      * @return mixed data which can be serialized by <b>json_encode</b>,
      * which is a value of any type other than a resource.
      * @since 5.4.0
    */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            "enabled" => $this->enabled,
            "title" => $this->getTitle(),
            "cardOperation" => $this->getCardOperation(),
            "cardStatementDescriptor" => $this->getCardStatementDescriptor(),
            "saveCards" => $this->isSaveCards(),
            "cardConfigs" => $this->getCardConfigs()
        ];
    }
}
