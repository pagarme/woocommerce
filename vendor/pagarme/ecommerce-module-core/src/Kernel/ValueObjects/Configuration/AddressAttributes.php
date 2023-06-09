<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class AddressAttributes extends AbstractValueObject
{
    /** @var string */
    private $street;
    /** @var string */

    private $number;
    /** @var string */

    private $neighborhood;
    /** @var string */

    private $complement;

    /**
     * AddressAttributes constructor.
     * @param string $street
     * @param string $number
     * @param string $neighborhood
     * @param string $complement
     */
    public function __construct($street, $number, $neighborhood, $complement)
    {
        $this->street = $street;
        $this->number = $number;
        $this->neighborhood = $neighborhood;
        $this->complement = $complement;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @return string
     */
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  AddressAttributes $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getStreet() === $object->getStreet() &&
            $this->getNumber() === $object->getNumber() &&
            $this->getNeighborhood() === $object->getNeighborhood() &&
            $this->getComplement() === $object->getComplement()
        ;
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

        $obj->street = $this->getStreet();
        $obj->number = $this->getNumber();
        $obj->neighborhood = $this->getNeighborhood();
        $obj->complement = $this->getComplement();

        return $obj;
    }
}
