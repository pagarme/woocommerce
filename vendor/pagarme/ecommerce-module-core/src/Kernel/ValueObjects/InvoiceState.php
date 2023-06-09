<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class InvoiceState extends AbstractValueObject
{
    const PAID = 'paid';
    const CANCELED = 'canceled';

    /**
     *
     * @var string
     */
    private $state;

    /**
     * OrderStatus constructor.
     *
     * @param string $state
     */
    private function __construct($state)
    {
        $this->setState($state);
    }

    static public function canceled()
    {
        return new self(self::CANCELED);
    }

    static public function paid()
    {
        return new self(self::PAID);
    }

    /**
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @param  string $status
     * @return OrderStatus
     */
    private function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  OrderState $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getState() === $object->getState();
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
        return $this->getState();
    }
}
