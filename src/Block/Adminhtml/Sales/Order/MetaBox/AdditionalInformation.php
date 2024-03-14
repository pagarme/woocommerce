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
use Woocommerce\Pagarme\Model\Charge as ChargeModel;
use Woocommerce\Pagarme\Model\Order;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class AdditionalInformation
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBox
 */
class AdditionalInformation extends AbstractMetaBox implements MetaBoxInterface
{
    /** @var string */
    protected $code = 'additional-information';

    /** @var int */
    protected $sortOrder = 2;

    /** @var string */
    protected $title = 'Pagar.me - Additional Information';

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string
     */
    protected $_template = 'templates/adminhtml/sales/order/meta-box/additional-information';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/payment/pix'];

    /**
     * @var ChargeModel
     */
    protected $charge;

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     * @param Order|null $order
     * @param ChargeModel|null $charge
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        Order $order = null,
        ChargeModel $charge = null
    ) {
        parent::__construct($jsonSerialize, $data);
        try {
            $this->order = $order ?? new Order($this->getOrderId());
        } catch (\Exception $e) {}
        $this->charge = $charge ?? new ChargeModel;
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
        if (!$this->getData('order_id')) {
            if (isset($this->getRequest()['id'])) {
                $this->setData('order_id', (int)$this->getRequest()['id']);
            }
            if (isset($this->getRequest()['post'])) {
                $this->setData('order_id', (int)$this->getRequest()['post']);
            }
        }
        return $this->getData('order_id');
    }

    /**
     * @return array
     */
    private function getRequest()
    {
        return $_REQUEST;
    }
}
