<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\CreditCard;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract Brands
 * @package Woocommerce\Pagarme\Model\Payment\CreditCard
 */
abstract class AbstractBrands
{
    /** @var string */
    protected $code = '';

    /** @var string */
    protected $name = '';

    /** @var int[] */
    protected $gaps = [4, 8, 12];

    /** @var int|int[] */
    protected $size = 16;

    /** @var string */
    protected $mask = '/(\\d{1,4})/g';

    /** @var int */
    protected $cvv = 3;

    /** @var int[] */
    protected $prefixes = [];

    /**
     * @return string
     */
    public function getBrandCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            'brand' => $this->code,
            'brandName' => $this->name,
            'gaps' => $this->gaps,
            'mask' => $this->mask,
            'size' => $this->size,
            'cvv' => $this->cvv,
            'prefixes' => $this->prefixes,
        ];
    }
}
