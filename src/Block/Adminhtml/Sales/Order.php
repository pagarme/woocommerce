<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales;

use Woocommerce\Pagarme\Block\Adminhtml\Sales\Order\MetaBoxes;
use Woocommerce\Pagarme\Block\Template;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Order
 * @package Woocommerce\Pagarme\Block\Checkout
 */
class Order extends Template
{
    /**
     * @var string
     */
    protected $_template = 'templates/adminhtml/sales/order';

    /**
     * @var MetaBoxes
     */
    private $metaBoxes;

    /**
     * @param MetaBoxes|null $metaBoxes
     * @param Json|null $jsonSerialize
     * @param array $data
     */
    public function __construct(
        MetaBoxes $metaBoxes = null,
        Json $jsonSerialize = null,
        array $data = []
    ) {
        parent::__construct($jsonSerialize, $data);
        $this->metaBoxes = $metaBoxes ?? new MetaBoxes;
    }

    /**
     * @return array
     */
    public function getMetaBoxes(): array
    {
        return $this->metaBoxes->getMetaBoxes();
    }


}
