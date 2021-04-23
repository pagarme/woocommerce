<?php

namespace Pagarme\Core\Kernel\Abstractions;

use JsonSerializable;

/**
 * The ValueObject Abstraction. It ensures that the value objects can be
 * structurally compared.
 *
 * All the value objects should extend this class.
 */
abstract class AbstractValueObject implements JsonSerializable
{
    /**
     * @param $name
     * @param $arguments
     * @throws \Exception
     */
    public static function __callStatic($name, $arguments)
    {
        if (!method_exists(static::class, $name)) {
            throw new \Exception(
                "Call to undefined method " . static::class .
                "::{$name}()",
                400
            );
        }
    }

    /**
     * Compares the object types and call the child structural comparison method.
     *
     * @param  mixed $object The object that will be compared.
     * @return bool
     */
    public function equals($object)
    {
        if (static::class === get_class($object)) {
            return $this->isEqual($object);
        }

        return false;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    abstract protected function isEqual($object);
}