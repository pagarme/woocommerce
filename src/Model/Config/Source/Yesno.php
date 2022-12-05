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
class Yesno implements OptionSourceInterface
{
    /** @var int */
    const VALUE_NO = 0;

    /** @var int */
    const VALUE_YES = 1;

    /** @var string */
    const LABEL_NO = 'No';

    /** @var string */
    const LABEL_YES = 'Yes';

    /** @var string[] */
    private $values = [
        self::VALUE_NO => self::LABEL_NO,
        self::VALUE_YES => self::LABEL_YES
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->values as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => __($label, 'woo-pagarme-payments')
            ];
        }
        return $options;
    }

    /**
     * "key-value" format
     * @return array
     */
    public function toArray()
    {
        return $this->values;
    }
}
