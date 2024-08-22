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

use Exception;
use Pagarme\Core\Kernel\Aggregates\Charge;
use Pagarme\Core\Kernel\Services\ChargeService;
use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\AbstractMetaBox;
use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBoxInterface;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Config;
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
    protected $code = 'capture';

    /** @var int */
    protected $sortOrder = 1;

    /** @var int */
    protected $title = 'Pagar.me - Charges';

    /** @var Config */
    private $config;

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

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     * @param Order|null $order
     * @param \Woocommerce\Pagarme\Model\Charge|null $charge
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Order $order = null,
        \Woocommerce\Pagarme\Model\Charge $charge = null,
        Config $config = null
    ) {
        parent::__construct($jsonSerialize, $data);
        try {
            $this->order = $order ?? new Order($this->getOrderId());
        } catch (\Exception $e) {}
        $this->charge = $charge ?? new \Woocommerce\Pagarme\Model\Charge;
        $this->config = $config ?? new Config();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return mixed|null
     */
    private function getOrderId()
    {
        if (isset($this->getRequest()['id'])) {
            return $this->getRequest()['id'];
        }
        if (isset($this->getRequest()['post'])) {
            return $this->getRequest()['post'];
        }
        return null;
    }

    /**
     * @return Charge[]|null
     * @throws Exception
     */
    public function getCharges($orderCode)
    {
        $chargeService = new ChargeService();
        if ($orderCode) {
            return $chargeService->findChargesByCode($orderCode);
        }

        return null;
    }

    public function getChargeUrl($chargeID)
    {
        if (!$this->config->isAccAndMerchSaved()) {
            return false;
        }

        return $this->config->getDashUrl() . 'charges/' . $chargeID;
    }

    /**
     * @return array
     */
    private function getRequest()
    {
        return $_REQUEST;
    }

    /**
     * @return string[]
     */
    public function getHeaderGrid()
    {
        return [
            'Created At',
            'Charge ID',
            'Type',
            "Total Amount",
            'Captured',
            'Refunded',
            'Status',
            'Action'
        ];
    }

    /**
     * @param $charge
     * @return false|mixed
     */
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
