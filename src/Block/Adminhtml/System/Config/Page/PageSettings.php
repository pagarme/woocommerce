<?php

/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare(strict_types=1);

namespace Woocommerce\Pagarme\Block\Adminhtml\System\Config\Page;

use Woocommerce\Pagarme\Controller\Gateways\AbstractGateway;

defined('ABSPATH') || exit;

class PageSettings
{
    protected $template = 'settings.phtml';

    protected $templatePath = 'templates/adminhtml/system/config/page/';

    /**
     * @var array
     */
    private $paymentMethods;

    /**
     * @var array
     */
    private $paymentGateways;

    /**
     * @var string
     */
    private $options;

    public function __construct(
        string $options,
        array $paymentMethods
    ) {
        $this->options = $options;
        $this->paymentMethods = $paymentMethods;
        $this->paymentGateways = $this->getPaymentGateways();
    }

    public function includeTemplate()
    {
        include plugin_dir_path(WCMP_ROOT_SRC) . $this->templatePath . $this->template;
    }

    /**
     * @return array
     */
    public function getPaymentMethods(): array
    {
        return $this->paymentMethods;
    }

    public function getPaymentGateways()
    {
        $paymentMethods = $this->getPaymentMethods();
        $gateways = array();

        foreach ($paymentMethods as $paymentMethod) {
            /** @var AbstractGateway $gateway */
            $gateway = new $paymentMethod();
            $gateways[$gateway->id] = $gateway;
        }
        return $gateways;
    }
}
