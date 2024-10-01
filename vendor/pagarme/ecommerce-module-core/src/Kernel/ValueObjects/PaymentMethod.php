<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

final class PaymentMethod extends AbstractValueObject
{
    const CREDIT_CARD = 'credit_card';
    const BOLETO = 'boleto';
    const DEBIT_CARD = 'debit_card';
    const VOUCHER = 'voucher';
    const PIX = 'pix';
    const GOOGLEPAY = 'googlepay';

    /**
     * @var string
     */
    private $paymentMethod;

    /**
     * PaymentMethod constructor.
     *
     * @param string $paymentMethod
     */
    private function __construct($paymentMethod)
    {
        $this->setPaymentMethod($paymentMethod);
    }

    static public function credit_card()
    {
        return new self(self::CREDIT_CARD);
    }

    static public function boleto()
    {
        return new self(self::BOLETO);
    }

    static public function debit_card()
    {
        return new self(self::DEBIT_CARD);
    }

    static public function voucher()
    {
        return new self(self::VOUCHER);
    }

    static public function pix()
    {
        return new self(self::PIX);
    }
    static public function googlepay()
    {
        return new self(self::GOOGLEPAY);
    }

    /**
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     *
     * @param string $paymentMethod
     * @return PaymentMethod
     */
    private function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param ChargeStatus $object
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
        return $this->paymentMethod;
    }
}
