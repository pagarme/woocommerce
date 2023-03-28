<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales\Order;

use Woocommerce\Pagarme\Block\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract MetaBoxes
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order
 */
abstract class AbstractMetaBox extends Template
{
    /** @var string */
    protected $code = '';

    /** @var int */
    protected $sortOrder = 1;

    /** @var int */
    protected $title = '';

    /** @var bool */
    protected $container;

    /**
     * @return string
     */
    public function getTitle()
    {
        return __($this->title, 'woo-pagarme-payments');
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return 'woo-pagarme-' . $this->code;
    }

    /** return int */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param bool $include
     * @return $this
     */
    public function setIncludeContainer(bool $include = true)
    {
        $this->container = $include;
        return $this;
    }

    /**
     * @return bool
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param string $content
     * @param array $element
     * @return string
     */
    public function formatElement(string $content, array $element)
    {
        if (!$this->container) {
            $content = '';
        }
        return wp_kses($content, $element);
    }
}
