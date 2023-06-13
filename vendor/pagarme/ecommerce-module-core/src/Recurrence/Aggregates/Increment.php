<?php

namespace Pagarme\Core\Recurrence\Aggregates;

use PagarmeCoreApiLib\Models\CreateIncrementRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;

class Increment extends AbstractEntity
{
    private $value;
    private $incrementType;
    private $cycles;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getIncrementType()
    {
        return $this->incrementType;
    }

    /**
     * @param mixed $incrementType
     */
    public function setIncrementType($incrementType)
    {
        $this->incrementType = $incrementType;
    }

    /**
     * @return mixed
     */
    public function getCycles()
    {
        return $this->cycles;
    }

    /**
     * @param mixed $cycles
     */
    public function setCycles($cycles)
    {
        $this->cycles = $cycles;
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

        $obj->value = $this->value;
        $obj->incrementType = $this->incrementType;

        return $obj;
    }

    /**
     * @return CreateIncrementRequest
     */
    public function convertToSDKRequest()
    {
        $incrementRequest = new CreateIncrementRequest();

        $incrementRequest->value = $this->getValue();
        $incrementRequest->incrementType = $this->getIncrementType();

        return $incrementRequest;
    }
}
