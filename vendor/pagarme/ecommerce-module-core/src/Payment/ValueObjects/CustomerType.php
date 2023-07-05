<?php

namespace Pagarme\Core\Payment\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class CustomerType extends AbstractValueObject
{
    const INDIVIDUAL = 'individual';
    const COMPANY = 'company';

    /** @var string */
    private $type;

    private function __construct($type)
    {
        $this->type = $type;
    }

    static public function individual()
    {
        return new self(self::INDIVIDUAL);
    }

    static public function company()
    {
        return new self(self::COMPANY);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
        return $this->type === $object->getType();
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
        return $this->type;
    }
}
