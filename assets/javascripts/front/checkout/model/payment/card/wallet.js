/* globals pagarmeCard */

let pagarmeCheckoutWallet = {
    cardSaveTarget: 'select[data-element="choose-credit-card"]',
    cardFields: [
        '[data-pagarme-element="fields-cc-data"]',
        '[data-element="save-cc-check"]',
        '[data-element="enable-multicustomers-check"]'
    ],
    onChangeCardWallet: function (event) {
        select = pagarmeCard.formatEventToJQuery(event);
        let wrapper = select.closest('fieldset');
        const method = select.val() ? 'slideUp' : 'slideDown';
        this.cardFields.forEach(function (field) {
            wrapper.find(field)[method]();
            wrapper.find(field).find('input').val('');
        });
        let brand = select.find('option:selected').data('brand');
        let brandInput = wrapper.find(pagarmeCard.brandTarget);
        brandInput.val(brand);
        if (select.val()) {
            pagarmeCard.updateInstallmentsElement(event);
        }
    },
    addEventListener: function (paymentTarget) {
        jQuery(this.cardSaveTarget).on('change', function (event) {
            pagarmeCheckoutWallet.onChangeCardWallet(event);
        });
    },
    start: function () {
        this.addEventListener();
    }
};
pagarmeCheckoutWallet.start();
