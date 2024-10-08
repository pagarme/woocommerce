<?php
/*
 * PagarmeCoreApiLib
 *
 * This file was automatically generated by APIMATIC v2.0 ( https://apimatic.io ).
 */

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;
use PagarmeCoreApiLib\Utils\DateTimeHelper;

/**
 *Recipient response
 */
class GetRecipientAddressResponse implements JsonSerializable
{

    /**
     * Street
     * @required
     * @maps street
     * @var string $street public property
     */
    public $street;
    /**
     * Complementary
     * @required
     * @maps complementary
     * @var string $complementary public property
     */
    public $complementary;
    /**
     * Street Number
     * @required
     * @maps street_number
     * @var string $streetNumber public property
     */
    public $streetNumber;
    /**
     * Neighborhood
     * @required
     * @maps neighborhood
     * @var string $neighborhood public property
     */
    public $neighborhood;
    /**
     * City
     * @required
     * @maps city
     * @var string $city public property
     */
    public $city;
    /**
     * State
     * @required
     * @maps state
     * @var string $state public property
     */
    public $state;
    /**
     * Zip Code
     * @required
     * @maps zip_code
     * @var string $zipCode public property
     */
    public $zipCode;
    /**
     * Reference Point
     * @required
     * @maps reference_point
     * @var string $referencePoint public property
     */
    public $referencePoint;

    /**
     * Constructor to set initial or default values of member properties
     
     */
    public function __construct()
    {
        if (func_num_args() == 8) {
            $this->street           = func_get_arg(0);
            $this->complementary    = func_get_arg(1);
            $this->streetNumber     = func_get_arg(2);
            $this->neighborhood     = func_get_arg(3);
            $this->city             = func_get_arg(4);
            $this->state            = func_get_arg(5);
            $this->zipCode          = func_get_arg(6);
            $this->referencePoint   = func_get_arg(7);
        }
    }


    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['street']             = $this->street;
        $json['complementary']      = $this->complementary;
        $json['street_number']      = $this->streetNumber;
        $json['neighborhood']       = $this->neighborhood;
        $json['city']               = $this->city;
        $json['state']              = $this->state;
        $json['zip_code']           = $this->zipCode;
        $json['reference_point']    = $this->referencePoint;

        return $json;
    }
}
