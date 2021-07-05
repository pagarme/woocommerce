<?php

namespace Woocommerce\Pagarme\Resource;

if (!function_exists('add_action')) {
    exit(0);
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Unirest\Request;
use Unirest\Request\Body;

class Charges extends Base
{
    const PATH = 'charges';

    /**
     * Capture charge
     *
     * @param int $charge_id
     * @param int $amount
     *
     * @return object Unirest\Response
     */
    public function capture($charge_id, $amount = 0)
    {
        $args = [];

        if (!empty($amount)) {
            $args['amount'] = $amount;
        }

        return Request::post(
            Base::URL . self::PATH . '/' . $charge_id . '/capture',
            $this->get_headers(),
            Body::Json($args)
        );
    }

    /**
     * Cancel charge
     *
     * @param int $charge_id
     * @param int $amount
     *
     * @return object Unirest\Response
     */
    public function cancel($charge_id, $amount = 0)
    {
        $args = [];

        if (!empty($amount)) {
            $args['amount'] = $amount;
        }

        return Request::delete(
            Base::URL . self::PATH . '/' . $charge_id,
            $this->get_headers(),
            Body::Json($args)
        );
    }
}
