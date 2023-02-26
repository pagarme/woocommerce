<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block;

use Woocommerce\Pagarme\Model\Data\DataObject;
use Woocommerce\Pagarme\Model\Serialize\Serializer\Json;

defined( 'ABSPATH' ) || exit;

/**
 * Class AbstractBlock
 * @package Woocommerce\Pagarme\Block
 */
abstract class AbstractBlock extends DataObject
{
    /** @var string */
    protected $_template = '';

    /** @var Json */
    protected $jsonSerialize;

    public function __construct(
        Json $jsonSerialize = null,
        array $data = []
    ) {
        $this->jsonSerialize = $jsonSerialize ?? new Json;
        parent::__construct($jsonSerialize, $data);
    }

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @param string $template
     * @return $this
     */
    protected function setTemplate(string $template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Produce and return block's html output
     * @return string
     */
    public function toHtml()
    {
        return $this->_toHtml();
    }

    /**
     * Override this method in descendants to produce html
     * @return string
     */
    protected function _toHtml()
    {
        return '';
    }
}
