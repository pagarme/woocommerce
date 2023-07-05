<?php

namespace Pagarme\Core\Payment\ValueObjects;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class PixBank extends AbstractValueObject
{
    const CODE_BB = '001';
    const CODE_SANTANDER = '033';
    const CODE_BRADESCO = '237';
    const CODE_ITAU = '341';
    const CODE_CITIBANK = '745';
    const CODE_CEF = '104';

    const NAME_BB = 'Bank of Brazil';
    const NAME_SANTANDER = 'Santander';
    const NAME_BRADESCO = 'Bradesco';
    const NAME_ITAU = 'Itau';
    const NAME_CITIBANK = 'Citibank';
    const NAME_CEF = 'Caixa Econômica Federal';

    const VALID_BANKS = ['BB', 'SANTANDER', 'BRADESCO', 'ITAU', 'CITIBANK', 'CEF'];

    /**
     * @var string
     */
    private $code;
    /**
     * @var string
     */
    private $name;

    /**
     * BoletoBank constructor.
     * @param string $code
     * @param string $name
     */
    private function __construct($code, $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    static public function bb()
    {
        return new self(self::CODE_BB, self::NAME_BB);
    }

    static public function santander()
    {
        return new self(self::CODE_SANTANDER, self::NAME_SANTANDER);
    }

    static public function bradesco()
    {
        return new self(self::CODE_BRADESCO, self::NAME_BRADESCO);
    }

    static public function itau()
    {
        return new self(self::CODE_ITAU, self::NAME_ITAU);
    }

    static public function citibank()
    {
        return new self(self::CODE_CITIBANK, self::NAME_CITIBANK);
    }

    static public function cef()
    {
        return new self(self::CODE_CEF, self::NAME_CEF);
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
        return
            $this->getCode() === $object->getCode() &&
            $this->getName() === $object->getName();
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
       $obj = new \stdClass();

       $obj->code = $this->getCode();
       $obj->name = $this->getName();

       return $obj;
    }
}
