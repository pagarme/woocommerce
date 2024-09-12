/* globals wc_pagarme_checkout */
/* jshint esversion: 6 */
let pagarmeCustomerFields = {
    billingDocumentField: jQuery('#billing_document'),
    shippingDocumentField: jQuery('#shipping_document'),
    documentMasks: ['000.000.000-00999', '00.000.000/0000-00'],
    documentMaskOptions: {
        onKeyPress: function (document, e, field, options) {
            const masks = pagarmeCustomerFields.documentMasks,
                mask = document.length > 14 ? masks[1] : masks[0];
            field.mask(mask, options);
        }
    },

    applyDocumentMask() {
        this.billingDocumentField.mask(this.documentMasks[0], this.documentMaskOptions);
        this.shippingDocumentField.mask(this.documentMasks[0], this.documentMaskOptions);
    },

    start: function () {
        this.applyDocumentMask();
    }
};

pagarmeCustomerFields.start();
