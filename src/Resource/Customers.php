<?php

namespace Woocommerce\Pagarme\Resource;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Customers extends Base
{
    const PATH = 'customers';

    /**
     * Create a new customer
     *
     * @param array $data fields to send
     *
     * @return object Unirest\Response
     */
    public function create(array $data)
    {
        $fields = array(
            'name',
            'email',
            'document',
            'phones',
            'type',
            'address',
            'code',
            'birthdate',
        );

        $args = $this->get_args($fields, $data);

        return Request::post(Base::URL . self::PATH, $this->get_headers(), Body::Json($args));
    }
}
