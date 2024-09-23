<?php

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

class CreateKycLinkResponse implements JsonSerializable
{
    /**
     * Url
     * @required
     * @var string $url public property
     */
    public $url;

    /**
     * Base64 QrCode
     * @required
     * @var string $base64_qrcode public property
     */
    public $base64_qrcode;

    /**
     * Expires at
     * @required
     * @var string $expires_at public property
     */
    public $expires_at;

    /**
     * Constructor to set initial or default values of member properties
     * @param string $url Initialization value for $this->url
     * @param string $base64_qrcode Initialization value for $this->base64_qrcode
     * @param array $expires_at Initialization value for $this->expires_at
     */
    public function __construct()
    {
        if (3 == func_num_args()) {
            $this->url = func_get_arg(0);
            $this->base64_qrcode = func_get_arg(1);
            $this->expires_at = func_get_arg(2);
        }
    }

    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['url'] = $this->url;
        $json['base64_qrcode'] = $this->base64_qrcode;
        $json['expires_at'] = $this->expires_at;

        return $json;
    }
}