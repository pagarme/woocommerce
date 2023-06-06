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
 * Class Pix
 * @package Woocommerce\Pagarme\Block\Checkout\Form
 */
class Pix extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/pix';

    /**
     * @param string $id
     * @return string
     */
    public function getElementId(string $id)
    {
        $id = '[pix][' . $id . ']';
        return parent::getElementId($id);
    }
}
