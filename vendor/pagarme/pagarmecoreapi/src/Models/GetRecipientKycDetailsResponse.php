<?php

namespace PagarmeCoreApiLib\Models;

use JsonSerializable;

class GetRecipientKycDetailsResponse implements JsonSerializable
{
    /**
     * Status
     * @required
     * @var string $status public property
     */
    public $status;

    /**
     * Status Reason
     * @required
     * @var string $status_reason public property
     */
    public $status_reason;

    /**
     * Constructor to set initial or default values of member properties
     * @param string $status Initialization value for $this->status
     * @param string $status_reason Initialization value for $this->status_reason
     */
    public function __construct()
    {
        if (2 == func_num_args()) {
            $this->status        = func_get_arg(0);
            $this->status_reason = func_get_arg(1);
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['status']        = $this->status;
        $json['status_reason'] = $this->status_reason;
        return $json;
    }
}
