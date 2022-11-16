<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Model\Payment\Billet;

use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 *  Class Banks
 * @package Woocommerce\Pagarme\Model\Payment\Billet
 */
class Banks
{
    /**
     * @return array
     */
    public function getbanks()
    {
        $this->autoLoad();
        $banks = [];
        foreach (get_declared_classes() as $class) {
            try {
                $reflect = new ReflectionClass($class);
                if($reflect->implementsInterface(BankInterface::class)) {
                    $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                    $banks[end($explodedFileName)] = $class;
                }
            } catch (\ReflectionException $e) {}
        }
        return $banks;
    }

    private function autoLoad()
    {
        foreach(glob( __DIR__ . '/Banks/*.php') as $file) {
            include_once($file);
        }
    }
}
