<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Data;

/**
 * Interface OptionSourceInterface
 * Source of option values in a form of value-label pairs
 * @package Woocommerce\Pagarme\Model\Data
 */
interface OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     * @return array Format: [['value' => '<value>', 'label' => '<label>'], ...]
     */
    public function toOptionArray();
}
