<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

declare( strict_types=1 );

namespace Woocommerce\Pagarme\Block\Adminhtml\Sales\Order;

use ReflectionClass;

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaBoxes
 * @package Woocommerce\Pagarme\Block\Adminhtml\Sales\Order
 */
class MetaBoxes
{
    /**
     * @param $code
     * @return MetaBoxInterface
     * @throws \Exception
     */
    public function getMetaBoxInstace($code)
    {
        foreach ($this->getMetaBoxesDeclared() as $class) {
            /** @var MetaBoxInterface $metaBox */
            $metaBox = new $class;
            if ($metaBox->getCode() === $code) {
                return $metaBox;
            }
        }
        throw new \Exception(__('Invalid MetaBox: ', 'woo-pagarme-payments') . $code);
    }

    /**
     * @return array
     */
    public function getMetaBoxesDeclared()
    {
        $this->autoLoad();
        $object = [];
        foreach (get_declared_classes() as $class) {
            try {
                $reflect = new ReflectionClass($class);
                if($reflect->implementsInterface(MetaBoxInterface::class)) {
                    $explodedFileName = explode(DIRECTORY_SEPARATOR, $reflect->getFileName());
                    $object[end($explodedFileName)] = $class;
                }
            } catch (\ReflectionException $e) {}
        }
        return $object;
    }

    /**
     * @param bool $sort
     * @return array
     */
    public function getMetaBoxes(bool $sort = true)
    {
        $object = [];
        foreach ($this->getMetaBoxesDeclared() as $class) {
            /** @var MetaBoxInterface $metaBox */
            $metaBox = new $class;
            if ($sort) {
                $sortOrder = $metaBox->getSortOrder();
                do {
                    $sortOrder++;
                } while(isset($object[$sortOrder]));
                $object[$sortOrder] = $metaBox;
            } else {
                $object[] = $metaBox;
            }
        }
        return $object;
    }

    /**
     * @return void
     */
    private function autoLoad()
    {
        foreach(glob( __DIR__ . '/MetaBox/*.php') as $file) {
            include_once($file);
        }
    }
}
