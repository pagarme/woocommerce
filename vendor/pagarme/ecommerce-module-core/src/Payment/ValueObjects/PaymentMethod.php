<?php

namespace Pagarme\Core\Payment\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class PaymentMethod extends AbstractValueObject
{
    const CREDIT_CARD = 'creditCard';
    const BOLETO = 'boleto';
    const TICKET = 'ticket';
    const VOUCHER = 'voucher';
    const BANK_TRANSFER = 'bankTransfer';
    const SAFETY_PAY = 'safetyPay';
    const CHECKOUT = 'checkout';
    const CASH = 'cash';
    const DEBIT_CARD = 'debitCard';
    const PIX = 'pix';
    const GOOGLEPAY = 'googlepay';

    private $method;

    private function __construct($method)
    {
        $this->method = $method;
    }

    public function getMethod()
    {
        return $this->method;
    }

    static public function creditCard()
    {
        return new self(self::CREDIT_CARD);
    }

    static public function debitCard()
    {
        return new self(self::DEBIT_CARD);
    }

    static public function boleto()
    {
        return new self(self::BOLETO);
    }

    static public function pix()
    {
        return new self(self::PIX);
    }
    static public function googlepay()
    {
        return new self(self::GOOGLEPAY);
    }

    static public function ticket()
    {
        return new self(self::TICKET);
    }

    static public function voucher()
    {
        return new self(self::VOUCHER);
    }

    static public function bankTransfer()
    {
        return new self(self::BANK_TRANSFER);
    }

    static public function safetyPay()
    {
        return new self(self::SAFETY_PAY);
    }

    static public function checkout()
    {
        return new self(self::CHECKOUT);
    }

    static public function cash()
    {
        return new self(self::CASH);
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getMethod() === $object->getMethod();
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
        return $this->getMethod();
    }
}



