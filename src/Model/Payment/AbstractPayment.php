<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract AbstractPayment
 * @package Woocommerce\Pagarme\Model\Payment
 */
abstract class AbstractPayment
{
    /** @var int */
    protected $suffix = null;

    /** @var string */
    protected $name = null;

    /** @var string */
    protected $code = null;

    /**
     * @return int
     * @throws \Exception
     */
    public function getSuffix()
    {
        if ($this->suffix) {
            return $this->suffix;
        }
        return $this->error($this->suffix);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getName()
    {
        if ($this->name) {
            return $this->name;
        }
        return $this->error($this->name);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getMethodCode()
    {
        if ($this->code) {
            return $this->code;
        }
        return $this->error($this->code);
    }

    /**
     * @param $field
     * @return mixed
     * @throws \Exception
     */
    private function error($field)
    {
        throw new \Exception(__('Invalid data for payment method: ', 'woo-pagarme-payments') . $field);
    }
}
