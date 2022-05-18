<?php

if (!function_exists('add_action')) {
    return;
}

use Woocommerce\Pagarme\Core;
use Woocommerce\Pagarme\Helper\Utils;
use Woocommerce\Pagarme\Model\Setting;

$suffix  = isset($suffix) ? $suffix : '';
$setting = Setting::get_instance();

?>

<div <?php echo
        /** phpcs:ignore */
        Utils::get_component('pagarme-checkout'); ?> data-pagarmecheckout-app-id="<?php echo esc_attr($setting->get_public_key()); ?>" data-pagarmecheckout-suffix="<?php echo !$suffix ? 1 : esc_html($suffix); ?>">

    <p class="form-row form-row-wide">

        <label for="voucher-card-holder-name">
            <?php esc_html_e('Card Holder Name', 'woo-pagarme-payments'); ?> <span class="required">*</span>
        </label>

        <input id="voucher-card-holder-name" onkeydown="return /[a-z, ]/i.test(event.key)" data-element="voucher-card-holder-name" data-required="true" class="input-text" data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="voucher-holder_name" style="font-size: 1.41575em">
    </p>

    <p class="form-row form-row-wide">

        <label for="voucher-card-number"><?php esc_html_e('Card number', 'woo-pagarme-payments'); ?> <span class="required">*</span></label>

        <input id="voucher-card-number" data-element="pagarme-voucher-card-number" class="input-text wc-credit-card-form-card-number pagarme-card-form-card-number" data-mask="0000 0000 0000 0000" placeholder="•••• •••• •••• ••••" data-required="true" data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="number" style="background-image: none">
        <input type="hidden" name="brand<?php echo esc_attr($suffix); ?>" data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="brand-input" />
        <span name="voucher-brand-image-<?php echo esc_attr(!$suffix ? 1 : $suffix); ?>" pagarme-suffix=<?php echo esc_attr($suffix); ?> data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="brand" data-pagarmecheckout-brand-image-<?php echo !$suffix ? 1 : esc_html($suffix); ?> data-pagarmecheckout-brand-<?php echo !$suffix ? 1 : esc_html($suffix); ?>></span>
    </p>

    <p class="form-row form-row-first">

        <label for="card-expiry">
            <?php esc_html_e('Expiration Date (MM/YY)', 'woo-pagarme-payments'); ?>
            <span class="required">*</span>
        </label>

        <input id="voucher-card-expiry" data-element="voucher-card-expiry" class="input-text wc-credit-card-form-card-expiry pagarme-card-form-card-expiry" data-mask="00 / 00" data-required="true" placeholder="<?php esc_html_e('MM / YY', 'woo-pagarme-payments'); ?>" data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="exp_date">
    </p>

    <p class="form-row form-row-last">

        <label for="voucher-card-cvc">
            <?php esc_html_e('Card code', 'woo-pagarme-payments'); ?> <span class="required">*</span>
        </label>

        <input id="voucher-card-cvc" data-element="voucher-card-cvc" data-mask="0000" class="input-text wc-credit-card-form-card-cvc pagarme-card-form-card-cvc" maxlength="4" placeholder="CVC" data-required="true" data-pagarmecheckout-element-<?php echo !$suffix ? 1 : esc_html($suffix); ?>="cvv">
    </p>

    <p class="form-row form-row-wide">
            <label>
                <?php esc_html_e('Card Holder Document Number', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="voucher-document-holder" class="input-text" id="voucher-document-holder">
            </label>
    </p>

</div>