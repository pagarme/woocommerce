<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBox;

use Pagarme\Core\Kernel\Aggregates\Charge;
use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\AbstractMetaBox;
use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBoxInterface;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class ChargeActions
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBox
 */
class ChargeActions extends AbstractMetaBox implements MetaBoxInterface
{
    /** @var string */
    protected $code = 'charge-actions';

    /** @var int */
    protected $sortOrder = 1;

    /** @var int */
    protected $title = 'Pagar.me - Capture/Cancellation';

    /**
     * @var Order
     */
    private $order;

    /** @var \Woocommerce\Pagarme\Model\Charge */
    private $charge;

    /**
     * @var string
     */
    protected $_template = 'templates/adminhtml/sales/order/meta-box/charge-actions';

    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Order $order = null,
        \Woocommerce\Pagarme\Model\Charge $charge = null
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->order = $order ?? new Order($this->getOrderId());
        $this->charge = $charge ?? new \Woocommerce\Pagarme\Model\Charge;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    private function getOrderId()
    {
        if (isset($this->getRequest()['post'])) {
            return $this->getRequest()['post'];
        }
        return null;
    }

    /**
     * @return false|Charge
     */
    public function getCharges()
    {
        return $this->getOrder()->get_charges();
    }

    /**
     * @return array
     */
    private function getRequest()
    {
        return $_REQUEST;
    }

    public function getHeaderGrid()
    {
        return [
            'Charge ID',
            'Type',
            "Total Amount",
            'Partially Captured',
            'Partially Canceled',
            'Partially Reversed',
            'Status',
            'Action'
        ];
    }

    public function getTransaction($charge)
    {
        return current($charge->getTransactions());
    }

    /**
     * @param $charge
     * @param string $type
     * @return String
     */
    public function getAmount($charge, string $type = '')
    {
        $method = 'get' . ucfirst($type) . 'Amount';
        return $charge->{$method}() ? Utils::format_order_price_to_view($charge->{$method}()) : '-';
    }

    /**
     * @return string[]
     */
    public function getTotals()
    {
        return [
            '',
            'paid',
            'canceled',
            'refunded'
        ];
    }

    /**
     * @return \Woocommerce\Pagarme\Model\Charge
     */
    public function getChargeInstance()
    {
        return $this->charge;
    }
}
