<?php

namespace Woocommerce\Pagarme\Resource;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Tokens extends Base
{
    const PATH = 'tokens';

    /**
     * Create a new a new credit card token
     *
     * @param array $data fields to send
     *
     * @return object Unirest\Response
     */
    public function create(array $data)
    {
        $fields = array(
            'type',
            'card',
        );

        $args = $this->get_args($fields, $data);

        return Request::post(
            Base::URL . self::PATH . '/?appId=' . $this->settings->get_public_key(),
            $this->get_headers(),
            Body::Json($args)
        );
    }
}
