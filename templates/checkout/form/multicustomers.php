<?php
/**
 * @author      Open Source Team
 * @copyright   2022 Pagar.me (https://pagar.me)
 * @license     https://pagar.me Copyright
 *
 * @link        https://pagar.me
 */

/** @var \Woocommerce\Pagarme\Block\Checkout\Form\Multicustomers $this */

declare( strict_types=1 );
?>
<fieldset data-ref="multicustomers-<?= $this->getPaymentInstance()->getMethodCode() . '-' . $this->getSequence() ?>" data-pagarme-payment="<?= $this->getPaymentInstance()->getMethodCode() ?>" data-pagarme-sequence="<?= $this->getSequence() ?>" data-pagarme-payment-element="multicustomers" style="display:none;">
    <?= $this->formatElement($this->getTitle(), ['h4' => []]) ?>
    <div class="multicustomer">
        <p class="form-row form-row-wide">
            <label>
                <?php esc_html_e('Full Name', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('name') ?>" data-required="true" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-wide">
            <label>
                <?php esc_html_e('Email', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('email') ?>" data-required="true" type="email" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-wide">
            <label>
                <?php esc_html_e('CPF', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('cpf') ?>" data-required="true" data-mask="000.000.000-00" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-first">
            <label>
                <?php esc_html_e('Zip Code', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('zip_code') ?>" data-required="true" data-mask="00000-000" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-last">
            <label>
                <?php esc_html_e('Street', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('street') ?>" data-required="true" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-first">
            <label>
                <?php esc_html_e('Number', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('number') ?>" data-required="true" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-last">
            <label>
                <?php esc_html_e('Neighborhood', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('neighborhood') ?>" data-required="true" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-wide">
            <label>
                <?php esc_html_e('Complement', 'woo-pagarme-payments'); ?>
                <input name="<?= $this->getElementId('complement') ?>" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-first">
            <label>
                <?php esc_html_e('City', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <input name="<?= $this->getElementId('city') ?>" data-required="true" class="input-text">
            </label>
        </p>
        <p class="form-row form-row-last">
            <label>
                <?php esc_html_e('State', 'woo-pagarme-payments'); ?> <span class="required">*</span>
                <select data-required="true" data-element="state" name="<?= $this->getElementId('state') ?>" style="padding: .6180469716em">
                    <?php
                    foreach ($this->getStates() as $uf => $state) {
                        printf('<option value="%s">%s</option>', esc_html($uf), esc_html($state));
                    }
                    ?>
                </select>
            </label>
        </p>
    </div>
</fieldset>
<script src="<?= esc_url($this->getFileUrl('assets/javascripts/front/checkout/model/multicustomers.js')); ?>" type="application/javascript"> </script>
