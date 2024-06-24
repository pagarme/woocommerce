<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;
use Pagarme\Core\Kernel\Exceptions\InvalidParamException;

final class Installment extends AbstractValueObject
{
    /**
     *
     * @var int
     */
    protected $times;
    /**
     *
     * @var int
     */
    protected $baseTotal;
    /**
     *
     * @var float
     */
    protected $interest;

    /**
     * Installment constructor.
     *
     * @param int $times
     * @param int $baseTotal
     * @param float $interest This should be expressed in real float value, not percentual value.
     *
     * @throws InvalidParamException
     */
    public function __construct($times, $baseTotal, $interest)
    {
        $this->setTimes($times);
        $this->setBaseTotal($baseTotal);
        $this->setInterest($interest);
    }

    /**
     *
     * @param  int $times
     * @return $this
     * @throws InvalidParamException
     */
    private function setTimes($times)
    {
        $newTimes = intval($times);
        if ($newTimes < 0 || $newTimes > 24) {
            throw new InvalidParamException(
                "A installment times should be set between 0 and 24!",
                $times
            );
        }
        $this->times = $newTimes;
        return $this;
    }

    /**
     *
     * @param  int $baseTotal
     * @return $this
     * @throws InvalidParamException
     */
    private function setBaseTotal($baseTotal)
    {
        $newBaseTotal = floatval($baseTotal);
        if ($newBaseTotal < 0) {
            throw new InvalidParamException(
                "A installment total price should be greater or equal to 0!",
                $baseTotal
            );
        }
        $this->baseTotal = $newBaseTotal;
        return $this;
    }

    /**
     *
     * @param  float $interest
     * @return $this
     * @throws InvalidParamException
     */
    private function setInterest($interest)
    {
        $this->interest = floatval($interest);
        return $this;
    }

    //calculated property getters
    /**
     *
     * @return int
     */
    public function getTotal()
    {
        $interest = (1 + $this->interest);
        $total = (float) $this->baseTotal * $interest;

        return round($total, 2);
    }
    /**
     *
     * @return int
     */
    public function getValue()
    {
        $total = (float) $this->getTotal() / $this->times;

        return round($total, 2);
    }

    //base property getters
    /**
     *
     * @return int
     */
    public function getTimes()
    {
        return $this->times;
    }
    /**
     *
     * @return int
     */
    public function getBaseTotal()
    {
        return $this->baseTotal;
    }
    /**
     *
     * @return float
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  Installment $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->getTimes() === $object->getTimes() &&
            $this->getBaseTotal() === $object->getBaseTotal() &&
            $this->getInterest() === $object->getInterest();
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
        $obj = new \stdClass;

        $obj->times = $this->getTimes();
        $obj->baseTotal = $this->getBaseTotal();
        $obj->interest = $this->getInterest();
        $obj->total = $this->getTotal();
        $obj->value = $this->getValue();

        return $obj;
    }
}
