<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

abstract class AbstractValidString extends AbstractValueObject
{
    /**
     *
     * @var string
     */
    protected $value;

    public function __construct($value)
    {
        $value = (string) $value;

        if (!is_string($value)) {
            throw new InvalidParamException("Value should be a string!", $value);
        }

        $this->setValue($value);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return AbstractValidString
     * @throws InvalidParamException
     */
    protected function setValue($value)
    {
        if (!is_string($value)) {
            throw new InvalidParamException("Value should be a string!", $value);
        }

        if ($this->validateValue($value)) {
            $this->value = $value;
            return $this;
        }

        throw new InvalidParamException("Invalid value for " . static::class . "!", $value);
    }

    abstract protected function validateValue($value);

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->value === $object->getValue();
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
        return $this->getValue();
    }

    public function __toString()
    {
        return $this->getValue();
    }
}
