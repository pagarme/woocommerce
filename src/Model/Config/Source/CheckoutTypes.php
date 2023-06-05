<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Config\Source;

use Woocommerce\Pagarme\Model\Data\OptionSourceInterface;

defined( 'ABSPATH' ) || exit;

/**
 * Class CheckoutTypes
 * @package Woocommerce\Pagarme\Model\Config\Source
 */
class CheckoutTypes extends AbstractOptions implements OptionSourceInterface
{
    /** @var string */
    const STANDART = 'Standart';

    /** @var int */
    const STANDART_VALUE = 'standart';

    /** @var string */
    const TRANSPARENT = 'Transparent';

    /** @var int */
    const TRANSPARENT_VALUE = 'transparent';
}
