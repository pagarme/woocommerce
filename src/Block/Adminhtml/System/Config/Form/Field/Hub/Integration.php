<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\Field\Hub;

use Woocommerce\Pagarme\Block\Adminhtml\System\Config\Form\AbstractField;
use Woocommerce\Pagarme\Helper\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class Select
 * @package Woocommerce\Pagarme\Block\Adminthml\System\Config\Form\Field
 */
class Integration extends AbstractField
{
    /** @var string */
    protected $template = 'hub/integration.phtml';

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

    /**
     * @return string
     */
    public function getIntegrationButtonLabel()
    {
        return ($this->config->getHubInstallId()) ? __('View Integration', 'woo-pagarme-payments') : __('Integrate With Pagar.me', 'woo-pagarme-payments');
    }

    /**
     * Check if current user has permission to desintegrate with Pagar.me Hub
     *
     * @return bool `true` if is an admin and if the account and merchant are saved, `false` otherwise
     */
    public function userCanDesintegrate()
    {
        return Utils::isCurrentUserAdmin() && $this->config->isAccAndMerchSaved();
    }
}
