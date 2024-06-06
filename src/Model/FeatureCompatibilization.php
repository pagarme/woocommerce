<?php

namespace Woocommerce\Pagarme\Model;

use HaydenPierce\ClassFinder\ClassFinder;
use Woocommerce\Pagarme\Block\ReactCheckout\AbstractPaymentMethodBlock;

class FeatureCompatibilization
{

    public function callCompatibilization()
    {
        foreach ($this->getFeatures() as $featureId => $state) {
            $this->addWoocommerceCompatibilization($featureId, $state);
        }
    }

    private function getFeatures()
    {
        return [
            'custom_order_tables'   => true,
            'analytics'             => false,
            'new_navigation'        => false,
            'product_block_editor'  => true,
            'cart_checkout_blocks'  => true,
            'woocommerce_custom_orders_table_enabled'           => true,
            'woocommerce_custom_orders_table_data_sync_enabled' => true,
        ];
    }

    public function addWoocommerceCompatibilization($featureId, $state)
    {
        if(!class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            return;
        }
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility($featureId, WCMP_PLUGIN_BASE, $state);
    }

    public static function isHposActivated()
    {
        if(!class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)) {
            return false;
        }
        if(!method_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class, 'custom_orders_table_usage_is_enabled')){
            return false;
        }
        return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    public function addSupportedBlocks()
    {
        if (
            !class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')
            || !class_exists('Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry')
        ) {
            return;
        }
        
        ClassFinder::disablePSR4Vendors();
        
        $blockClasses = ClassFinder::getClassesInNamespace(
            'Woocommerce\Pagarme\Block\ReactCheckout',
            ClassFinder::RECURSIVE_MODE
        );
        
        $blockClasses = array_filter($blockClasses, [$this, 'filterAbstractClasses']);
        $blockClasses = preg_filter('/^/', '\\', $blockClasses);

        add_action(
            'woocommerce_blocks_payment_method_type_registration',
            function(\Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $paymentMethodRegistry)
                use($blockClasses) {
                foreach ($blockClasses as $blockClass) {
                    $paymentMethodRegistry->register(new $blockClass());
                }
            }
        );
    }

    public function filterAbstractClasses($className)
    {
        return strpos($className, 'Abstract') === false;
    }
}
