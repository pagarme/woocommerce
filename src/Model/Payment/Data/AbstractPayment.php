<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Data;

use Woocommerce\Pagarme\Model\Data\DataObject;

defined( 'ABSPATH' ) || exit;

/**
 * Class AbstractPayment
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class AbstractPayment extends DataObject
{
    /** @var string */
    protected $identifier = '';

    /**
     * @return void
     */
    protected function init()
    {
        if ($this->getPostPaymentContent() && is_array($this->getPostPaymentContent()) && array_key_exists($this->identifier, $this->getPostPaymentContent())) {
            foreach ($this->getPostPaymentContent()[$this->identifier] as $field => $value) {
                $this->{$this->getMethod($field)}($value);
            }
        }
    }

    /**
     * @return array|null
     */
    protected function getPostPaymentContent()
    {
        $content = null;
        if (isset($_POST[WCMP_PREFIX])) {
            $pagarme = $_POST[WCMP_PREFIX];
            $content = isset($pagarme[$this->getPaymentMethod()]) ? $pagarme[$this->getPaymentMethod()] : null;
        }
        return $content;
    }

    /**
     * @param string $value
     * @param string $type
     * @return string
     */
    protected function getMethod(string $value, string $type = 'set')
    {
        return $type . str_replace(' ', '', ucwords(str_replace('_', ' ', $this->convertField($value))));
    }

    /**
     * @return string
     */
    protected function getPaymentMethod()
    {
        return isset($_POST['payment_method']) ? str_replace('woo-pagarme-payments-', '', $_POST['payment_method']) : null;
    }

    /**
     * @param string $field
     * @return string
     */
    public function convertField(string $field)
    {
        return str_replace('-', '_', ucwords(str_replace('_', ' ', $field)));
    }

    /**
     * @param string $method
     * @param bool $identifier
     * @return bool
     */
    protected function havePaymentForm(string $method, bool $identifier = true)
    {
        $content = $this->getPostPaymentContent();
        if ($identifier && isset($this->getPostPaymentContent()[$this->identifier])) {
            $content = $this->getPostPaymentContent()[$this->identifier];
        }
        if (is_array($content) && array_key_exists($method, $content)) {
            return true;
        }
        return false;
    }
}
