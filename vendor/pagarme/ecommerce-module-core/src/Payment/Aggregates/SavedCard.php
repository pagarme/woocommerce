<?php

namespace Pagarme\Core\Payment\Aggregates;

use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\ValueObjects\CardBrand;
use Pagarme\Core\Kernel\ValueObjects\Id\CustomerId;
use Pagarme\Core\Kernel\ValueObjects\NumericString;

final class SavedCard extends AbstractEntity
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var CustomerId */
    private $ownerId;

    /** @var OwnerName */
    private $ownerName;

    /** @var NumericString */
    private $firstSixDigits;

    /** @var NumericString */
    private $type;

    /** @var NumericString */
    private $lastFourDigits;

    /** @var CardBrand */
    private $brand;

    /** @var \DateTime */
    private $createdAt;

    /**
     * @return CustomerId
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param CustomerId $ownerId
     */
    public function setOwnerId(CustomerId $ownerId)
    {
        $this->ownerId = $ownerId;
    }

    /**
     * @return OwnerName
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * @param OwnerName $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return NumericString
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param NumericString $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return NumericString
     */
    public function getFirstSixDigits()
    {
        return $this->firstSixDigits;
    }

    /**
     * @param NumericString $firstSixDigits
     */
    public function setFirstSixDigits($firstSixDigits)
    {
        $this->firstSixDigits = $firstSixDigits;
    }

    /**
     * @return NumericString
     */
    public function getLastFourDigits()
    {
        return $this->lastFourDigits;
    }

    /**
     * @param NumericString $lastFourDigits
     */
    public function setLastFourDigits(NumericString $lastFourDigits)
    {
        $this->lastFourDigits = $lastFourDigits;
    }

    /**
     * @return CardBrand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param CardBrand $brand
     */
    public function setBrand(CardBrand $brand)
    {
        $this->brand = $brand;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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
        $obj = new \stdClass();

        $obj->id = $this->getId();
        $obj->pagarmeId = $this->getPagarmeId();
        $obj->ownerId = $this->getOwnerId();
        $obj->type = $this->getType();
        $obj->ownerName = $this->getOwnerName();
        $obj->firstSixDigits = $this->getFirstSixDigits();
        $obj->lastFourDigits = $this->getLastFourDigits();
        $obj->brand = $this->getBrand();
        $obj->createdAt = $this->getCreatedAt();

        if ($obj->createdAt !== null) {
            $obj->createdAt = $obj->createdAt->format(self::DATE_FORMAT);
        }

        return $obj;
    }
}
