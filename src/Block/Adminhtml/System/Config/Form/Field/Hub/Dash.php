<?php
/**
 * @author      Open Source Team
 * @copyright   2025 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub;

use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\AbstractField;

defined( 'ABSPATH' ) || exit;

/**
 * Class Dash
 * @package Woocommerce\Pagarme\Block\Adminthml\System\Config\Form\Field
 */
class Dash extends AbstractField
{
    /** @var string */
    protected $template = 'hub/dash.phtml';

    /**
     * @return void
     */
    public function elementCallBack()
    {
        $this->setCurrent($this->getDefault());
        if ($value = $this->config->getData($this->getId())) {
            $this->setCurrent($value);
        }
        parent::includeTemplate();
    }
}
