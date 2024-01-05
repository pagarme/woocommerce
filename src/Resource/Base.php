<?php

namespace Woocommerce\Pagarme\Resource;

if (!function_exists('add_action')) {
    exit(0);
}

use Unirest\Request;
use Woocommerce\Pagarme\Model\Config;

abstract class Base
{
    /**
     * @var \Woocommerce\Pagarme\Model\Config
     */
    protected $settings;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Base URL
     */
    const URL = 'https://api.mundipagg.com/core/v1/';

    public function __construct($auth = true)
    {
        $this->config = new Config();

        Request::verifyPeer(false);
        Request::verifyHost(false);

        if ($auth) {
            $this->auth();
        }
    }

    /**
     * Set Basic Authentication Header on Unirest/Request
     *
     * @return void
     */
    public function auth()
    {
        Request::auth($this->config->getSecretKey(), '');
    }

    /**
     * Get default headers
     *
     * @param string|null $idempotencyKey
     * @return array
     */
    protected function get_headers($idempotencyKey = null)
    {
        return array(
            'Accept' => 'application/json',
            'content-type' => 'application/json; charset=utf-8',
            'idempotency-key' => $idempotencyKey
        );
    }

    /**
     * Get a key of an array if it exists.
     *
     * @param string $key
     * @param \array|null $data
     *
     * @return mixed
     */
    protected function get($key, array $data = null)
    {
        if (empty($data) || !isset($data[$key])) {
            return null;
        }

        return $data[$key];
    }

    /**
     * Build parameters for make the request
     *
     * @param array $properties
     * @param array $data
     * @return array
     */
    protected function get_args(array $properties, array $data)
    {
        $args = array(
            'metadata' => array(
                'module_name' => 'WooCommerce',
                'module_version' => WCMP_VERSION,
            ),
        );

        foreach ($properties as $property) {
            $args[$property] = call_user_func([$this, 'get'], $property, $data);
        }

        return $args;
    }
}
