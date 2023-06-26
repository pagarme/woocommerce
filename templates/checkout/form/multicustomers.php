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
<fieldset data-ref="multicustomers-<?= $this->getPaymentInstance()->getMethodCode() . '-' . $this->getSequence() ?>"
          data-pagarme-payment="<?= $this->getPaymentInstance()->getMethodCode() ?>"
          data-pagarme-sequence="<?= $this->getSequence() ?>" data-pagarme-payment-element="multicustomers"
          style="display:none;">
    <legend></legend>
    <?= $this->formatElement($this->getTitle(), ['h4' => []]) ?>
    <div class="multicustomer">
        <p class="form-row form-row-wide">
            <label for="<?= $this->getElementId('name') ?>">
                <?php esc_html_e('Full Name', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('name') ?>" name="<?= $this->getElementId('name') ?>"
                   data-required="true" class="input-text">
        </p>
        <p class="form-row form-row-wide">
            <label for="<?= $this->getElementId('email') ?>">
                <?php esc_html_e('Email', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('email') ?>" name="<?= $this->getElementId('email') ?>"
                   data-required="true" type="email" class="input-text">
        </p>
        <p class="form-row form-row-wide">
            <label for="<?= $this->getElementId('cpf') ?>">
                <?php esc_html_e('CPF', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('cpf') ?>" name="<?= $this->getElementId('cpf') ?>"
                   data-required="true" data-mask="000.000.000-00" class="input-text">
        </p>
        <p class="form-row form-row-first">
            <label for="<?= $this->getElementId('zip_code') ?>">
                <?php esc_html_e('Zip Code', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('zip_code') ?>"
                   name="<?= $this->getElementId('zip_code') ?>" data-required="true" data-mask="00000-000"
                   class="input-text">
        </p>
        <p class="form-row form-row-last">
            <label for="<?= $this->getElementId('street') ?>">
                <?php esc_html_e('Street', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('street') ?>" name="<?= $this->getElementId('street') ?>"
                   data-required="true" class="input-text">
        </p>
        <p class="form-row form-row-first">
            <label for="<?= $this->getElementId('number') ?>">
                <?php esc_html_e('Number', 'woo-pagarme-payments'); ?> <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('number') ?>" name="<?= $this->getElementId('number') ?>"
                   data-required="true" class="input-text">
        </p>
        <p class="form-row form-row-last">
            <label for="<?= $this->getElementId('complement') ?>">
                <?php esc_html_e('Complement', 'woo-pagarme-payments'); ?>
            </label>
            <input type="text" id="<?= $this->getElementId('complement') ?>"
                   name="<?= $this->getElementId('complement') ?>" class="input-text">
        </p>
        <p class="form-row form-row-first">
            <label for="<?= $this->getElementId('neighborhood') ?>">
                <?php esc_html_e('Neighborhood', 'woo-pagarme-payments'); ?> <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('neighborhood') ?>"
                   name="<?= $this->getElementId('neighborhood') ?>" data-required="true" class="input-text">
        </p>
        <p class="form-row form-row-last">
            <label for="<?= $this->getElementId('city') ?>">
                <?php esc_html_e('City', 'woo-pagarme-payments'); ?> <span class="required">*</span>
            </label>
            <input type="text" id="<?= $this->getElementId('city') ?>" name="<?= $this->getElementId('city') ?>"
                   data-required="true" class="input-text">
        </p>
        <p class="form-row form-row-wide">
            <label for="<?= $this->getElementId('state') ?>">
                <?php esc_html_e('State', 'woo-pagarme-payments'); ?>
                <span class="required">*</span>
            </label>
            <select data-required="true" data-element="state" id="<?= $this->getElementId('state') ?>"
                    name="<?= $this->getElementId('state') ?>">
                <?php
                foreach ($this->getStates() as $uf => $state) {
                    printf('<option value="%s">%s</option>', esc_html($uf), esc_html($state));
                }
                ?>
            </select>
        </p>
    </div>
</fieldset>
<script type="text/javascript">
    if (typeof pagarmeMultiCustomer == 'object') {
        pagarmeMultiCustomer.start();
    }
</script>
