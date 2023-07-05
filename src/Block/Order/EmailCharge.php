<?php
/**
 * @author      Open Source Team
 * @copyright   2023 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Order;

use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Charge;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined('ABSPATH') || exit;

/**
 * Class EmailCharge
 * @package Woocommerce\Pagarme\Block\Order
 */
class EmailCharge extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/order/email-charge';

    private $basePath = '\Woocommerce\Pagarme\Block\Order\Email\\';

    /** @var Charge */
    private $modelCharge;

    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Charge $modelCharge = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->modelCharge = $modelCharge ?? new Charge;
    }

    public function getEmailClass($transaction)
    {
        $class = $this->getPaymentMethod($transaction->getTransactionType()->getType());
        if (!class_exists($this->basePath . $class)) {
            $class = 'DefaultEmail';
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
    public function getEmailType($transaction)
    {
        if ($transaction && $transaction->getTransactionType()->getType()) {
            return str_replace(' ', '', ucwords(
                str_replace('_', ' ', $transaction->getTransactionType()->getType())
            ));
        }
    }
}
