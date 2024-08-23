<?php

namespace PagarmeCoreApiLib\Models;

use stdClass;

class CreateRegisterInformationPhoneRequest implements \JsonSerializable
{
    /**
     * DDD
     * @required
     * @maps ddd
     * @var string $ddd public property
     */
    public $ddd;

    /**
     * Number
     * @required
     * @maps number
     * @var string $number public property
     */
    public $number;

    /**
     * Type
     * @required
     * @maps type
     * @var string $type public property
     */
    public $type;

    /**
     * @param string $ddd
     * @param string $number
     * @param string $type
     */
    public function __construct($ddd, $number, $type)
    {
        $this->ddd = $ddd;
        $this->number = $number;
        $this->type = $type;
    }

    /**
     * Encode this object to JSON
     *
     * @param bool $asArrayWhenEmpty Whether to serialize this model as an array whenever no fields
     *        are set. (default: false)
     *
     * @return array|stdClass
     */
    #[\ReturnTypeWillChange] // @phan-suppress-current-line PhanUndeclaredClassAttribute for (php < 8.1)
    public function jsonSerialize()
    {
        $json = [];
        $json['ddd']    = $this->ddd;
        $json['number'] = $this->number;
        $json['type']   = $this->type;

        return $json;
    }
}
