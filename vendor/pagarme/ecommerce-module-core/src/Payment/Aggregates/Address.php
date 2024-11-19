<?php

namespace Pagarme\Core\Payment\Aggregates;

use PagarmeCoreApiLib\Models\CreateAddressRequest;
use Pagarme\Core\Kernel\Abstractions\AbstractEntity;
use Pagarme\Core\Kernel\Helper\StringFunctionsHelper;
use Pagarme\Core\Kernel\Services\LocalizationService;
use Pagarme\Core\Payment\Interfaces\ConvertibleToSDKRequestsInterface;

final class Address extends AbstractEntity implements ConvertibleToSDKRequestsInterface
{
    const ADDRESS_LINE_SEPARATOR = ',';

    /**
     * @var string
     */
    private $number;
    /**
     * @var string
     */
    private $street;
    /**
     * @var string
     */
    private $neighborhood;
    /**
     * @var string
     */
    private $complement;
    /**
     * @var string
     */
    private $zipCode;
    /**
     * @var string
     */
    private $city;
    /**
     * @var string
     */
    private $country;
    /** @var string */
    private $state;

    /** @var LocalizationService */
    protected $i18n;

    public function __construct()
    {
        $this->i18n = new LocalizationService();
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Address
     * @throws \Exception
     */
    public function setNumber($number)
    {
        $numberWithoutComma = str_replace(
            self::ADDRESS_LINE_SEPARATOR,
            '',
            $number ?? ''
        );

        $numberWithoutLineBreaks = StringFunctionsHelper::removeLineBreaks(
            $numberWithoutComma
        );

        $this->number = substr($numberWithoutLineBreaks, 0, 15);

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param string $street
     * @return Address
     * @throws \Exception
     */
    public function setStreet($street)
    {
        $streetWithoutLineBreaks = StringFunctionsHelper::removeLineBreaks(
            $street
        );

        $this->street = substr($streetWithoutLineBreaks, 0, 64);

        if (empty($this->street)) {
            $inputName = $this->i18n->getDashboard('street');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood ?? '';
    }

    /**
     * @param string $neighborhood
     * @return Address
     * @throws \Exception
     */
    public function setNeighborhood($neighborhood)
    {
        $neighborhoodWithoutComma = str_replace(
            self::ADDRESS_LINE_SEPARATOR,
            '',
            $neighborhood ?? ''
        );

        $neighborhoodWithoutLineBreaks = StringFunctionsHelper::removeLineBreaks(
            $neighborhoodWithoutComma
        );

        $this->neighborhood = substr($neighborhoodWithoutLineBreaks, 0, 64);

        return $this;
    }

    /**
     * @return string
     */
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * @param string $complement
     * @return Address
     */
    public function setComplement($complement)
    {
        $complementWithoutLineBreaks = StringFunctionsHelper::removeLineBreaks($complement);
        $this->complement = substr($complementWithoutLineBreaks, 0, 64);
        return $this;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     * @return Address
     */
    public function setZipCode($zipCode)
    {
        $zipCode = trim($zipCode);

        if (empty($zipCode)) {
            $inputName = $this->i18n->getDashboard('zipCode');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        $this->zipCode = $this->formatZipCode($zipCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Address
     * @throws \Exception
     */
    public function setCity($city)
    {
        $this->city = trim(
            substr($city, 0, 64)
        );

        if (empty($this->city)) {

            $inputName = $this->i18n->getDashboard('city');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return Address
     * @throws \Exception
     */
    public function setCountry($country)
    {
        $this->country = substr($country, 0, 2);

        if (empty($this->country)) {

            $inputName = $this->i18n->getDashboard('country');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    public function getLine1()
    {
        if ($this->getNumber()) {
            $line[] = $this->getNumber();
        }

        $line[] = $this->getStreet();

        if ($this->getNeighborhood()) {
            $line[] = $this->getNeighborhood();
        }

        return implode(self::ADDRESS_LINE_SEPARATOR, $line);
    }

    public function getLine2()
    {
        return $this->complement;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Address
     * @throws \Exception
     */
    public function setState($state)
    {
        $this->state = substr($state, 0, 2);

        if (empty($this->state)) {

            $inputName = $this->i18n->getDashboard('state');
            $message = $this->i18n->getDashboard(
                "The %s should not be empty!",
                $inputName
            );

            throw new \Exception($message, 400);
        }

        return $this;
    }

    /**
      * Specify data which should be serialized to JSON
      * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
      * @return \stdClass data which can be serialized by <b>json_encode</b>,
      * which is a value of any type other than a resource.
      * @since 5.4.0
    */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $obj = new \stdClass();

        $obj->street = $this->street;
        $obj->number = $this->number;
        $obj->complement = $this->complement;
        $obj->neighborhood = $this->neighborhood;
        $obj->zipCode = $this->zipCode;
        $obj->city = $this->city;
        $obj->state = $this->state;
        $obj->country = $this->country;
        $obj->line1 = $this->getLine1();
        $obj->line2 = $this->getLine2();

        return $obj;
    }

    /**
     * @return CreateAddressRequest
     */
    public function convertToSDKRequest()
    {
        $addressRequest = new CreateAddressRequest();

        $addressRequest->street = $this->getStreet();
        $addressRequest->number = $this->getNumber();
        $addressRequest->complement = $this->getComplement();
        $addressRequest->neighborhood = $this->getNeighborhood();
        $addressRequest->city = $this->getCity();
        $addressRequest->state = $this->getState();
        $addressRequest->country = $this->getCountry();
        $addressRequest->zipCode = $this->getZipCode();
        $addressRequest->line1 = $this->getLine1();
        $addressRequest->line2 = $this->getLine2();

        return $addressRequest;
    }

    private function formatZipCode($zipCode)
    {
        $zipCode = str_replace('-', '', $zipCode ?? '');
        $this->country = $this->country ?? "BR";
        $brazilianZipCodeLength = 8;
        if (strtoupper($this->country) === 'BR') {
            $zipCode = sprintf("%0{$brazilianZipCodeLength}s", $zipCode);
            $zipCode = substr($zipCode, 0, $brazilianZipCodeLength);
            return $zipCode;
        }

        $zipCode = substr($zipCode, 0, 16);
        return $zipCode;
    }
}
