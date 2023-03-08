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
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Card
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Card extends AbstractPayment
{
    /** @var int */
    private $num;

    /** @var Multicustomers|null */
    private $multicustomers;

    /**
     * @param int $num
     * @param Json|null $jsonSerialize
     * @param array $data
     * @param Multicustomers|null $multicustomers
     */
    public function __construct(
        $num = 1,
        Json  $jsonSerialize = null,
        array $data = [],
        Multicustomers $multicustomers = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->num = $num;
        $this->multicustomers = $multicustomers ?? new Multicustomers;
        $this->init();
    }

    /**
     * @return void
     */
    protected function init()
    {
        foreach ($this->getPostPaymentContent()['cards'][$this->num] as $field => $value) {
            $this->{$this->getMethod($field)}($value);
        }
    }

    protected function setInstallment($data)
    {
        $value = 1;
        if ($data) {
            $value = $data;
        }
        return $this->setData('installment', $value);
    }

    /**
     * @param string $method
     * @param bool $identifier
     * @return bool
     */
    protected function havePaymentForm(string $method, bool $identifier = true)
    {
        if ($this->getPostPaymentContent()['cards'][$this->num] && is_array($this->getPostPaymentContent()['cards'][$this->num]) && array_key_exists($method, $this->getPostPaymentContent()['cards'][$this->num])) {
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setMulticustomers($data)
    {
        if ($this->havePaymentForm(Multicustomers::FIELD) && $this->multicustomers->isEnable($data)) {
            return $this->setData(Multicustomers::FIELD, $this->multicustomers->setData($data));
        }
        return $this;
    }
}
