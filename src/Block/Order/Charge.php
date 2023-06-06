<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Order;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Charge
 * @package Woocommerce\Pagarme\Block\Order
 */
class Charge extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/charge';

    private $basePath = '\Woocommerce\Pagarme\Block\Order\Transaction\\';

    /** @var \Woocommerce\Pagarme\Model\Charge */
    private $modelCharge;

    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        \Woocommerce\Pagarme\Model\Charge $modelCharge = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->modelCharge = $modelCharge ?? new \Woocommerce\Pagarme\Model\Charge;
    }

    public function getTransactionClass($transaction)
    {
        $class = $this->getPaymentMethod($transaction->getTransactionType()->getType());
        if (!class_exists($this->basePath . $class)) {
            $class = 'DefaultTransaction';
        }
        return $this->basePath . $class;
    }

    public function getChargeStatus()
    {
        return $this->modelCharge->get_i18n_status($this->getCharge()->getStatus()->getStatus());
    }

    /**
     * @param string $method
     * @return array|string|string[]
     */
    protected function getPaymentMethod(string $method)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));
    }

    /**
     * @return array|string|string[]|void
     */
    public function getTransactionType($transaction)
    {
        if ($transaction && $transaction->getTransactionType()->getType()) {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', $transaction->getTransactionType()->getType())));
        }
    }
}
