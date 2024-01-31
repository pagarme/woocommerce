<?php

namespace Woocommerce\Pagarme\Model;

use HaydenPierce\ClassFinder\ClassFinder;
use Woocommerce\Pagarme\Block\NewCheckout\AbstractPaymentMethodBlock;

class WoocommerceSupportedBlocks
{
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
            'Woocommerce\Pagarme\Block\NewCheckout',
            ClassFinder::RECURSIVE_MODE
        );
        
        $abstracBlockKey = array_search(AbstractPaymentMethodBlock::class, $blockClasses);
        if ($abstracBlockKey !== false) {
          unset($blockClasses[$abstracBlockKey]);
        }
        
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
}