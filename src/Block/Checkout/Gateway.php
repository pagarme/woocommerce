<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Checkout;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Gateway as GatewayModel;
use Woocommerce\Pagarme\Model\Payment\PaymentInterface;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;
use Woocommerce\Pagarme\Model\Checkout;
use Woocommerce\Pagarme\Core;

defined('ABSPATH') || exit;

/**
 * Class Gateway
 * @package Woocommerce\Pagarme\Block\Checkout
 */
class Gateway extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/default';

    /** @var bool */
    protected $container;

    /**
     * @param GatewayModel $gateway
     * @param Config|null $config
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        Json         $jsonSerialize = null,
        array        $data = [],
        GatewayModel $gateway = null,
        Config       $config = null
    )
    {
        parent::__construct($jsonSerialize, $data);
        if (!$this->getData('config')) {
            $this->setData('config', $config ?? new Config);
        }
        if (!$this->getData('gateway')) {
            $this->setData('gateway', $gateway ?? new GatewayModel);
        }
    }

    /**
     * @param PaymentInterface $payment
     * @return $this
     */
    public function setPaymentInstance(PaymentInterface $payment)
    {
        return $this->setData('payment_instance', $payment);
    }

    /**
     * @return PaymentInterface
     */
    public function getPaymentInstance()
    {
        return $this->getData('payment_instance');
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return $this->getData('gateway')->getConfigDataProvider();
    }

    /**
     * @param string $id
     * @return string | null
     */
    public function getElementId(string $id)
    {
        if (!$this->getPaymentInstance()){
            return null;
        }
        return WCMP_PREFIX . '[' . $this->getPaymentInstance()->getMethodCode() . ']' . $id;
    }

    public function getPaymentClass()
    {
        return '\Woocommerce\Pagarme\Block\Checkout\Payment\\' . str_replace(' ', '', ucwords($this->numeralReplace(str_replace(['_', '-'], ' ', $this->getPaymentInstance()->getMethodCode()))));
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
     * @return string
     */
    public function getHomeUrl()
    {
        return get_home_url(null, '/wc-api/' . Checkout::API_REQUEST);
    }

    /**
     * @param string $file
     * @return string
     */
    public function getFileUrl(string $file)
    {
        return Core::plugins_url($file);
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->getData('config');
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
     * @return bool|int|mixed|string|\WC_Tax
     */
    public function getCartTotals()
    {
        global $wp;

        if (!isset($wp->query_vars['order-pay'])) {
            return WC()->cart->total;
        }
        $orderId = $wp->query_vars['order-pay'];
        $order = wc_get_order($orderId);
        return $order->get_total();
    }

    /**
     * @return bool
     */
    public function showMessage()
    {
        return $this->getData('show_message') ?? false;
    }

    /**
     * @return string|null
     */
    public function getMessage($htmlFormat = false)
    {
        if (!$this->showMessage()) {
            return;
        }
        $content = $this->getPaymentInstance()->getMessage();
        if ($htmlFormat) {
            $content = "<p>{$content}</p>";
        }
        return $content;
    }

    /**
     * @return bool
     */
    public function showImage()
    {
        return $this->getData('show_image') ?? false;
    }


    /**
     * @param $htmlFormat
     * @return string|null
     */
    public function getImage($htmlFormat = false)
    {
        if (!$this->showImage()) {
            return;
        }
        $content = $this->getPaymentInstance()->getImage();
        if ($htmlFormat) {
            $name = esc_html__($this->getPaymentInstance()->getName(), 'woo-pagarme-payments');
            $content = "<p><img class='logo' src='{$content}' alt='{$name}' title='{$name}' /></p>";
        }

        return $content;
    }

    /**
     * @return bool
     */
    public function showOrderValue()
    {
        return $this->getData('show_order_value') ?? false;
    }

    /**
     * @return array|mixed|true
     */
    public function showMulticustomersForm()
    {
        return $this->getData('show_multicustomers_form') ?? true;
    }
}
