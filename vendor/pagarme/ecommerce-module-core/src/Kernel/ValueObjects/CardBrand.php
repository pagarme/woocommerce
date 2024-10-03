<?php

namespace Pagarme\Core\Kernel\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

/** @todo there is a way to make the enumeration behavior of classes like this abstract.
 */
final class CardBrand extends AbstractValueObject
{
    CONST NO_BRAND = "noBrand";

    CONST VISA = "Visa";
    CONST MASTERCARD = "Mastercard";
    CONST AMEX = "Amex";
    CONST HIPERCARD = "HiperCard";
    CONST DINERS = "Diners";
    CONST ELO = "Elo";
    CONST DISCOVER = "Discover";
    CONST AURA = "Aura";
    CONST JCB = "JCB";
    CONST CREDZ = "Credz";
    CONST SODEXO_ALIMENTACAO = "SodexoAlimentacao";
    CONST SODEXO_CULTURA = "SodexoCultura";
    CONST SODEXO_GIFT = "SodexoGift";
    CONST SODEXO_PREMIUM = "SodexoPremium";
    CONST SODEXO_REFEICAO = "SodexoRefeicao";
    CONST SODEXO_COMBUSTIVEL = "SodexoCombustivel";
    CONST TICKET = "Ticket";
    CONST VR = "VR";
    CONST ALELO = "Alelo";
    CONST BANESE = "Banese";
    CONST CABAL = "Cabal";
    CONST SODEXO = "Sodexo";

    private $name;

    private function __construct($name)
    {
        $this->setName($name);
    }

    private function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    static public function nobrand()
    {
        return new self(self::NO_BRAND);
    }

    static public function visa()
    {
        return new self(self::VISA);
    }

    static public function mastercard()
    {
        return new self(self::MASTERCARD);
    }

    static public function amex()
    {
        return new self(self::AMEX);
    }

    static public function hipercard()
    {
        return new self(self::HIPERCARD);
    }

    static public function diners()
    {
        return new self(self::DINERS);
    }

    static public function elo()
    {
        return new self(self::ELO);
    }

    static public function discover()
    {
        return new self(self::DISCOVER);
    }

    static public function aura()
    {
        return new self(self::AURA);
    }

    static public function jcb()
    {
        return new self(self::JCB);
    }

    static public function credz()
    {
        return new self(self::CREDZ);
    }

    static public function sodexoalimentacao()
    {
        return new self(self::SODEXO_ALIMENTACAO);
    }

    static public function sodexocultura()
    {
        return new self(self::SODEXO_CULTURA);
    }

    static public function sodexogift()
    {
        return new self(self::SODEXO_GIFT);
    }

    static public function sodexopremium()
    {
        return new self(self::SODEXO_PREMIUM);
    }

    static public function sodexorefeicao()
    {
        return new self(self::SODEXO_REFEICAO);
    }

    static public function sodexocombustivel()
    {
        return new self(self::SODEXO_COMBUSTIVEL);
    }

    static public function ticket()
    {
        return new self(self::TICKET);
    }

    static public function vr()
    {
        return new self(self::VR);
    }

    static public function alelo()
    {
        return new self(self::ALELO);
    }

    static public function banese()
    {
        return new self(self::BANESE);
    }

    static public function cabal()
    {
        return new self(self::CABAL);
    }

    static public function sodexo()
    {
        return new self(self::SODEXO);
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  CardBrand $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return $this->getName() === $object->getName();
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
        return $this->getName();
    }
}
