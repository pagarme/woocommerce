<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class TransactionType extends AbstractValueObject
{
    const CREDIT_CARD = "credit_card";
    const BOLETO = "boleto";
    const VOUCHER = "voucher";
    const DEBIT_CARD = "debit_card";
    const PIX = 'pix';
    /**
     *
     * @var string
     */
    private $type;

    /**
     * TransactionType constructor.
     *
     * @param string $type
     */
    private function __construct($type)
    {
        $this->setType($type);
    }

    public static function creditCard()
    {
        return new self(self::CREDIT_CARD);
    }

    public static function boleto()
    {
        return new self(self::BOLETO);
    }

    public static function voucher()
    {
        return new self(self::VOUCHER);
    }

    public static function debitCard()
    {
        return new self(self::DEBIT_CARD);
    }

    public static function pix()
    {
        return new self(self::PIX);
    }

    /**
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @param  string $type
     * @return TransactionType
     */
    private function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  TransactionType $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getType() === $object->getType();
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
        return $this->type;
    }
}
