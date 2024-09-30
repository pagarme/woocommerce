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

use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Card
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Googlepay extends AbstractPayment
{

    private $token;
    /** @var Multicustomers|null */
    private $multicustomers;

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     * @param Multicustomers|null $multicustomers
     */
    public function __construct(
        Json  $jsonSerialize = null,
        array $data = [],
        Multicustomers $multicustomers = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->multicustomers = $multicustomers ?? new Multicustomers;
        $this->init();
    }

    protected function init() {
        $this->{$this->getMethod('token')}($this->getPostPaymentContent()['googlepay']['payload']);
    }

    protected function setToken($data)
    {
        $this->setData('token', $data);
        return $this->token = $data;
    }

    public function getToken()
    {
        return $this->token;
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
