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

use Woocommerce\Pagarme\Block\Checkout\Gateway;

defined( 'ABSPATH' ) || exit;

/**
 * Class Billet
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Billet extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/billet';

    /**
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        $id = '[billet][' . $id . ']';
        return parent::getElementId($id);
    }
}
