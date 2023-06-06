<?php

namespace Woocommerce\Pagarme\Controller\Gateways\Exceptions;

use WC_Data_Exception;

class InvalidOptionException extends WC_Data_Exception
{
    const code = "invalid-payment-pagarme-option";

    public function __construct($code, $message, $http_status_code = 400, $data = array())
    {
        parent::__construct($code, $message, $http_status_code, $data);
    }
}
