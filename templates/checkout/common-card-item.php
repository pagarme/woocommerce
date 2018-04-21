<?php

if ( ! function_exists( 'add_action' ) ) {
    return;
}

use Woocommerce\Mundipagg\Core;
use Woocommerce\Mundipagg\Helper\Utils;
use Woocommerce\Mundipagg\Model\Setting;

$suffix  = isset( $suffix ) ? $suffix: '';
$setting = Setting::get_instance();

?>

<div <?php echo Utils::get_component( 'mundipagg-checkout' ); ?> 
    data-mundicheckout-app-id="<?php echo $setting->get_public_key(); ?>"
    data-mundicheckout-suffix="<?php echo ! $suffix ? 1 : $suffix; ?>">

    <p class="form-row form-row-wide">

        <label for="card-holder-name">
            <?php _e( 'Card Holder Name', Core::TEXTDOMAIN ); ?> <span class="required">*</span>
        </label>

        <input id="card-holder-name"
                data-element="card-holder-name"
                data-required="true"
                class="input-text wc-credit-card-form-card-expiry"
                data-mundicheckout-element="holder_name">
    </p>

    <p class="form-row form-row-wide">

        <label for="card-number"><?php _e( 'Card number', Core::TEXTDOMAIN ); ?> <span class="required">*</span></label>

        <input id="card-number"
                data-element="card-number"
                class="input-text wc-credit-card-form-card-expiry"
                data-mask="0000000000000000000"
                placeholder="•••• •••• •••• ••••"
                data-required="true"
                data-mundicheckout-element="number">
        <input type="hidden" name="brand<?php echo $suffix; ?>" data-mundicheckout-element="brand-input"/>
        <span data-mundicheckout-element="brand"
                data-mundicheckout-brand-image
                data-mundicheckout-brand></span>
    </p>

    <p class="form-row form-row-first">

        <label for="card-expiry">
            <?php _e( 'Expiry (MM/YY)', Core::TEXTDOMAIN ); ?>
            <span class="required">*</span>
        </label>

        <input id="card-expiry" data-element="card-expiry"
                class="input-text wc-credit-card-form-card-expiry"
                data-mask="00/00"
                data-required="true"
                placeholder="<?php _e( 'MM / YY', Core::TEXTDOMAIN ); ?>"
                data-mundicheckout-element="exp_date">
    </p>

    <p class="form-row form-row-last">

        <label for="card-cvc">
            <?php _e( 'Card code', Core::TEXTDOMAIN ); ?> <span class="required">*</span>
        </label>

        <input id="card-cvc"
                data-element="card-cvc"
                data-mask="0000"
                class="input-text wc-credit-card-form-card-cvc"
                maxlength="4"
                placeholder="CVC"
                style="width:100px"
                data-required="true"
                data-mundicheckout-element="cvv">
    </p>

</div>