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
class EnvironmentsTypes extends AbstractOptions implements OptionSourceInterface
{
    /** @var string */
    const PRODUCTION = 'Production';

    /** @var int */
    const PRODUCTION_VALUE = 'Production';

    /** @var string */
    const SANDBOX = 'Sandbox';

    /** @var int */
    const SANDBOX_VALUE = 'Sandbox';
}
