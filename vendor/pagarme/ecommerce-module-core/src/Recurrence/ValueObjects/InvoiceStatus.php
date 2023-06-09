<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class InvoiceStatus extends AbstractValueObject
{
    const PAID = 'paid';
    const CANCELED = 'canceled';
    const PENDING = 'pending';

    /**
     * @var string
     */
    private $status;

    /**
     * SubscriptionStatus constructor.
     *
     * @param string $status
     */
    private function __construct($status)
    {
        $this->setStatus($status);
    }

    public static function paid()
    {
        return new self(self::PAID);
    }

    public static function canceled()
    {
        return new self(self::CANCELED);
    }

    public static function pending()
    {
        return new self(self::PENDING);
    }

    /**
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     *
     * @param  string $status
     * @return SubscriptionStatus
     */
    private function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  OrderStatus $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getStatus() === $object->getStatus();
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
        return $this->getStatus();
    }
}
