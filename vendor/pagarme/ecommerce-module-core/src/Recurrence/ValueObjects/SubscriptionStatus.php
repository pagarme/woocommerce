<?php

namespace Pagarme\Core\Recurrence\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class SubscriptionStatus extends AbstractValueObject
{
    const ACTIVE = 'active';
    const CANCELED = 'canceled';
    const FUTURE = 'future';
    const FAILED = 'failed';
    const CHARGEDBACK = 'chargedback';

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

    public static function active()
    {
        return new self(self::ACTIVE);
    }

    public static function canceled()
    {
        return new self(self::CANCELED);
    }

    public static function future()
    {
        return new self(self::FUTURE);
    }

    public static function failed()
    {
        return new self(self::FAILED);
    }
    
    public static function chargedback()
    {
        return new self(self::CHARGEDBACK);
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
