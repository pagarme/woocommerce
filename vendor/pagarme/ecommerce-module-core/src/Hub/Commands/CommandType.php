<?php

namespace Pagarme\Core\Hub\Commands;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class CommandType extends AbstractValueObject
{
    const SANDBOX = 'Sandbox';
    const PRODUCTION = 'Production';
    const DEVELOPMENT = 'Development';

    /**
     *
     * @var string
     */
    private $value;

    public static function Sandbox()
    {
        return new self(self::SANDBOX);
    }

    public static function Production()
    {
        return new self(self::PRODUCTION);
    }

    public static function Development()
    {
        return new self(self::DEVELOPMENT);
    }

    private function __construct($value)
    {
        $this->setValue($value);
    }

    /**
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     *
     * @param  string $value
     * @return CommandType
     */
    private function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     *
     * @var static $object
     */
    public function isEqual($object)
    {
        return $this->value === $object->getValue();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->value;
    }
}
