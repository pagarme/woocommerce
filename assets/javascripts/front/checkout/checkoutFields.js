/* globals wc_pagarme_checkout */
/* jshint esversion: 6 */
const pagarmeCustomerFields = {
    billingDocumentId: 'billing_document',
    shippingDocumentId: 'shipping_document',
    billingPagarmeDocumentId: 'billing-address-document',
    shippingPagarmeDocumentId: 'shipping-address-document',
   
    documentMasks: [
        '000.000.000-00999',
        '00.000.000/0000-00'
    ],
    documentMaskOptions: {
        onKeyPress: function (document, e, field, options) {
            const masks = pagarmeCustomerFields.documentMasks,
                mask = document.length > 14 ? masks[1] : masks[0];
            field.mask(mask, options);
        }
    },

    applyDocumentMask() {
        jQuery('#' + this.billingDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
        jQuery('#' + this.shippingDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
        jQuery('#' + this.billingPagarmeDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
        jQuery('#' + this.shippingPagarmeDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
    },

    addEventListener() {
        jQuery(document.body).on('DOMContentLoaded', function () {
            pagarmeCustomerFields.applyDocumentMask();
        });
        jQuery(document.body).on('checkout_error', function () {
            const documentFieldIds = [
                    pagarmeCustomerFields.billingDocumentId,
                    pagarmeCustomerFields.shippingDocumentId,
                    pagarmeCustomerFields.billingPagarmeDocumentId,
                    pagarmeCustomerFields.shippingPagarmeDocumentId
                ];
            jQuery.each(documentFieldIds, function () {
                const documentField = '#' + this + '_field',
                    isDocumentEmpty = jQuery('.woocommerce-error li[data-id="' + this + '"]').length,
                    isDocumentInvalid = jQuery('.woocommerce-error li[data-pagarme-error="' + this + '"]').length;
                if (isDocumentEmpty || isDocumentInvalid) {
                    jQuery(documentField).addClass('woocommerce-invalid');
                } else {
                    jQuery(documentField).removeClass('woocommerce-invalid');
                }
            });
        });
    },

    start: function () {
        this.applyDocumentMask();
        this.addEventListener();
    }
};

pagarmeCustomerFields.start();