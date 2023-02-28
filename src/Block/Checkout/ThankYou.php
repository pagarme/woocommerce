<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout;

use Woocommerce\Pagarme\Block\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Class ThankYou
 * @package Woocommerce\Pagarme\Block\Checkout
 */
class ThankYou extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/thank-you';

    /** @var bool */
    protected $container;

    /**
     * @return string
     */
    public function getThankYouClass()
    {
        return '\Woocommerce\Pagarme\Block\Checkout\ThankYou\\' . str_replace(' ', '', ucwords($this->numeralReplace(str_replace(['_', '-'], ' ', $this->getPaymentMethod()))));
    }

    /**
     * @param string $class
     * @return array|string|string[]
     */
    public function numeralReplace(string $class)
    {
        return str_replace(
            ['1', '2', '3'],
            ['one', 'two', 'tree'],
            $class
        );
    }

    /**
     * @param string $content
     * @param array $element
     * @return string
     */
    public function formatElement(string $content, array $element)
    {
        if (!$this->container) {
            $content = '';
        }
        return wp_kses($content, $element);
    }

    /**
     * @param bool $include
     * @return $this
     */
    public function setIncludeContainer(bool $include = true)
    {
        $this->container = $include;
        return $this;
    }

    /**
     * @return bool
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return mixed|null
     */
    public function getResponseData()
    {
        if ($order = $this->getPagarmeOrder()) {
            if (property_exists($order, 'response_data')) {
                $response = $order->response_data;
                if (is_string($response)) {
                    $response = json_decode($response);
                }
                return $response;
            }
        }
        return null;
    }

    /**
     * @return bool
     */
    public function isSuccessOrder()
    {
        if (!in_array($this->getPagarmeOrder()->pagarme_status, ['failed', 'canceled'])) {
            return true;
        }
        return false;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getFilePath(string $path)
    {
        return esc_url(plugins_url($path, WCMP_ROOT_FILE));
    }
}
