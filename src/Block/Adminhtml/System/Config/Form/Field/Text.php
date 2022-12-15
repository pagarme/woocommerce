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
class Text extends AbstractField
{
    /** @var string */
    protected $template = 'text.phtml';

    /** @var string */
    private $label;

    /**
     * @param string $label
     * @return Text
     */
    public function setLabel(
        string $label
    ) {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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
