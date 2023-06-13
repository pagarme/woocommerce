<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class OrderState extends AbstractValueObject
{
    const STATE_NEW = 'new';
    const PENDING_PAYMENT = 'pending_payment';
    const PROCESSING = 'processing';
    const COMPLETE = 'complete';
    const CLOSED = 'closed';
    const CANCELED = 'canceled';
    const HOLDED = 'holded';
    const PAYMENT_REVIEW = 'payment_review';

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

    static public function stateNew()
    {
        return new self(self::STATE_NEW);
    }

    static public function pendingPayment()
    {
        return new self(self::PENDING_PAYMENT);
    }

    static public function processing()
    {
        return new self(self::PROCESSING);
    }

    static public function complete()
    {
        return new self(self::COMPLETE);
    }

    static public function closed()
    {
        return new self(self::CLOSED);
    }

    static public function canceled()
    {
        return new self(self::CANCELED);
    }

    static public function holded()
    {
        return new self(self::HOLDED);
    }

    static public function paymentReview()
    {
        return new self(self::PAYMENT_REVIEW);
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
