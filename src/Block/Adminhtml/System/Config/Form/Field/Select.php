<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field;

use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\AbstractField;

defined( 'ABSPATH' ) || exit;

/**
 * Class Select
 * @package Woocommerce\Pagarme\Block\Adminthml\System\Config\Form\Field
 */
class Select extends AbstractField
{
    /** @var string */
    protected $template = 'select.phtml';

    /** @var array */
    private $options = [];

    /**
     * @param array $options
     * @return Select
     */
    public function setOptions(
        array $options
    ) {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return void
     */
    public function elementCallBack()
    {
        if ($value = $this->config->getData($this->getId())) {
            $this->setCurrent($value);
        }
        parent::includeTemplate();
    }
}
