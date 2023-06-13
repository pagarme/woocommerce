<?php

namespace Pagarme\Core\Payment\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class Discounts extends AbstractValueObject
{
    CONST FLAT = 'flat';
    CONST PERCENTAGE = 'percentage';

    /**
     * @var string
     */
    private $discount_type;

    /**
     * @var int
     */
    private $value;

    /**
     * @var int
     */
    private $cycles;

    /**
     * Discount constructor.
     * @param $discountType
     * @param $value
     * @param $cycles
     */
    private function __construct($discountType, $value, $cycles)
    {
        $this->discount_type = $discountType;
        $this->value = $value;
        $this->cycles = $cycles;
    }

    /**
     * @param $value
     * @param $cycles
     * @return Discount
     */
    public static function percentage($value, $cycles)
    {
        return new self(self::PERCENTAGE, $value, $cycles);
    }

    /**
     * @param $value
     * @param $cycles
     * @return Discount
     */
    public static function flat($value, $cycles)
    {
        return new self(self::FLAT, $value, $cycles);
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discount_type;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getCycle()
    {
        return $this->cycles;
    }

    /**
     * @param Discount $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return get_object_vars($this) === (array)$object;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
