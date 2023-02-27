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
 * Class Billet
 * @package Woocommerce\Pagarme\Model\Payment\Data
 */
class Billet extends AbstractPayment
{
    /** @var Multicustomers|null */
    private $multicustomers;

    /** @var string */
    protected $identifier = 'billet';

    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Multicustomers $multicustomers = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->multicustomers = $multicustomers ?? new Multicustomers;
        $this->init();
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
