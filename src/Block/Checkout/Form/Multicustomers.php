<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Form;

use WC_Countries;
use Woocommerce\Pagarme\Block\Checkout\Gateway;
use Woocommerce\Pagarme\Model\Config;
use Woocommerce\Pagarme\Model\Gateway as GatewayModel;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class Multicustomers
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Multicustomers extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/multicustomers';

    /**
     * @var string[]
     */
    protected $scripts = ['checkout/model/multicustomers'];

    /** @var int  */
    protected $sequence = 1;

    /**
     * @param Json|null $jsonSerialize
     * @param array $data
     * @param GatewayModel|null $gateway
     * @param Config|null $config
     * @param WC_Countries|null $countries
     */
    public function __construct(
        Json $jsonSerialize = null,
        array $data = [],
        GatewayModel $gateway = null,
        Config $config = null,
        WC_Countries $countries = null
    ) {
        parent::__construct($jsonSerialize, $data, $gateway, $config);
        if (!$countries) {
            $countries = new WC_Countries;
        }
        $this->setData('countries', $countries);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title') ?? '';
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        return $this->setData('title', $title);
    }

    /**
     * @param int $sequence
     * @return Multicustomers
     */
    public function setSequence(int $sequence)
    {
        return $this->setData('sequence', $sequence);
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        if (!$this->getData('sequence')) {
            return $this->sequence;
        }
        return $this->getData('sequence');
    }

    /**
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        if ($this->getParentElementId()) {
            return $this->getParentElementId() . '[' . $id . ']';
        }
        $id = '[multicustomers][' . $this->getSequence() . '][' . $id . ']';
        return parent::getElementId($id);
    }

    /**
     * @return mixed
     */
    public function getStates()
    {
        return $this->getData('countries')->get_states('BR');
    }
}
