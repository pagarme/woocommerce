<?php
/**
 * Select field view.
 *
 * @package Extra_Checkout_Fields_For_Brazil/Admin/View
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
        <p><?php echo esc_attr($hub_environment); ?></p>
<?php if ($is_sandbox_mode) : ?>
            <div class="pagarme-message-warning">
                        <span>
                            <?= __('Important! This store is linked to the Pagar.me test environment. This environment is intended for integration validation and does not generate real financial transactions.', 'woo-pagarme-payments'); ?>
                        </span>
            </div>
<?php endif; ?>
