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
 * Class Yesno
 * @package Woocommerce\Pagarme\Model\Config\Source
 */
class Yesno extends AbstractOptions implements OptionSourceInterface
{
    /** @var string */
    const NO = 'No';

    /** @var int */
    const NO_VALUE = 0;

    /** @var string */
    const YES = 'Yes';

    /** @var int */
    const YES_VALUE = 1;
}
