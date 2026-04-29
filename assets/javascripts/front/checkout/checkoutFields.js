/* globals wc_pagarme_checkout */
/* jshint esversion: 6 */
const pagarmeCustomerFields = {
    billingDocumentId: 'billing_document',
    shippingDocumentId: 'shipping_document',
    blocksBillingDocumentId: 'billing-address-document',
    blocksShippingDocumentId: 'shipping-address-document',

    documentMasks: [
        'AAA.AAA.AAA-AAAAAA', // CPF
        'AA.AAA.AAA/AAAA-AA' // CNPJ
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
        jQuery('#' + this.blocksBillingDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
        jQuery('#' + this.blocksShippingDocumentId).mask(this.documentMasks[0], this.documentMaskOptions);
    },

    addEventListener() {
        jQuery(document.body).on('checkout_error', function () {
            const documentFieldIds = [
                    pagarmeCustomerFields.billingDocumentId,
                    pagarmeCustomerFields.shippingDocumentId,
                    pagarmeCustomerFields.blocksBillingDocumentId,
                    pagarmeCustomerFields.blocksShippingDocumentId
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
        this.addEventListener();

        setTimeout(function() {
            pagarmeCustomerFields.applyDocumentMask();
        }, 5000);
    }
};

pagarmeCustomerFields.start();
