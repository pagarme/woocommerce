<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Exception;
use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class PricingSchemeValueObject extends AbstractValueObject
{
    const UNIT = 'unit';
    const PACKAGE = 'package';
    const VOULME = 'volume';
    const TIER = 'tier';

    /** @var string */
    private $price;
    private $schemeType;

    protected function __construct($type, $value)
    {
        $this->setSchemeType($type);
        $this->setPrice($value);
    }

    private function setSchemeType($type)
    {
        $this->schemeType = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSchemeType()
    {
        return $this->schemeType;
    }

    /**
     * @param mixed $schemeType
     */

    /**
     * @param $value
     * @return DiscountValueObject
     */
    public static function unit($value)
    {
        return new PricingSchemeValueObject(
            self::UNIT,
            $value
        );
    }

    /**
     * @param string $type
     * @return DueValueObject
     * @throws Exception
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
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
            $this->getPrice() === $object->getPrice() &&
            $this->getSchemeType() === $object->getSchemeType();
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

        $obj->price = $this->getPrice();
        $obj->schemeType = $this->getSchemeType();

        return $obj;
    }
}
