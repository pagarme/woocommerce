<?php

namespace Woocommerce\Pagarme\Controller\Gateways\Exceptions;

use WC_Data_Exception;

class InvalidOptionException extends WC_Data_Exception
{
    const CODE = "invalid-payment-pagarme-option";

    public function __construct($code, $message, $httpStatusCode = 400, $data = array())
    {
        parent::__construct($code, $message, $httpStatusCode, $data);
    }
}
