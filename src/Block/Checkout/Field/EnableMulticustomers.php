<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Checkout\Field;

use Woocommerce\Pagarme\Block\Checkout\Gateway;

defined( 'ABSPATH' ) || exit;

/**
 * Class EnableMulticustomers
 * @package Woocommerce\Pagarme\Block\Checkout\Field
 */
class EnableMulticustomers extends Gateway
{
    /**
     * @var string
     */
    protected $_template = 'templates/checkout/form/field/enable-multicustomers';

    public function getElementId(string $id)
    {
        $id = '[multicustomer][' . $id . ']';
        return parent::getElementId($id);
    }
}
