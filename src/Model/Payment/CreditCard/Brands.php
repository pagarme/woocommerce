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

use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Brands
 * @package Woocommerce\Pagarme\Model\Payment\Voucher
 */
class Brands
{
    /**
     * @return array
     */
    public function getBrands()
    {
        $this->autoLoad();
        $banks = [];
        foreach (get_declared_classes() as $class) {
            try {
                $reflect = new ReflectionClass($class);
                if($reflect->implementsInterface(BrandsInterface::class)) {
                    $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                    $banks[end($explodedFileName)] = $class;
                }
            } catch (\ReflectionException $e) {}
        }
        return $banks;
    }

    private function autoLoad()
    {
        foreach(glob( __DIR__ . '/Brands/*.php') as $file) {
            include_once($file);
        }
    }
}
