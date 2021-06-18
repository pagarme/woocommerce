<?php

namespace Woocommerce\Pagarme\Resource;

if (!function_exists('add_action')) {
    exit(0);
}

use Unirest\Exception;
use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Orders extends Base
{
    const PATH = 'orders';

    /**
     * Create a new order
     *
     * @param array $data fields to send
     *
     * @return object Unirest\Response
     * @throws Exception
     */
    public function create(array $data)
    {
        $fields = array(
            'code',
            'items',
            'customer_id',
            'customer',
            'shipping',
            'payments',
            'closed',
            'antifraud_enabled',
        );

        $args = $this->get_args($fields, $data);

        return Request::post(
            Base::URL . self::PATH,
            $this->get_headers($data['idempotencyKey']),
            Body::Json($args)
        );
    }
}
