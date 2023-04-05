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
 * Class AbstractOptions
 * @package Woocommerce\Pagarme\Model\Config\Source
 */
abstract class AbstractOptions implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $reflectionClass = new \ReflectionClass($this);
        $const = $reflectionClass->getConstants();
        foreach ($const as $code => $value) {
            if (strpos($code, '_VALUE') === false) {
                $options[] = ['value' => $const[$code . '_VALUE'], 'label' => __($value)];
            }
        }
        return $options;
    }

    /**
     * "key-value" format
     * @return array
     */
    public function toArray()
    {
        $options = [];
        $reflectionClass = new \ReflectionClass($this);
        $const = $reflectionClass->getConstants();
        foreach ($const as $code => $value) {
            if (strpos($code, '_VALUE') === false) {
                $options[$const[$code . '_VALUE']] =  __($value);
            }
        }
        return $options;
    }

    /**
     * "key-value" format
     * @return array
     */
    public function toLabelsArray($translate = false)
    {
        $options = [];
        $reflectionClass = new \ReflectionClass($this);
        $const = $reflectionClass->getConstants();
        foreach ($const as $code => $value) {
            if (strpos($code, '_VALUE') === false) {
                $options[strtolower($value)] = $translate ? __($value) : $value;
            }
        }
        return $options;
    }
}
