/* globals pagarmeCard */
/* jshint esversion: 8 */
let pagarmeOrderValue = {
    valueTarget: 'input[data-pagarme-element="order-value"]',
    firstCardFieldset: 'fieldset[data-pagarmecheckout-card-num="1"]',
    fillAnotherInput: async function (event) {
        let input = pagarmeCard.formatEventToJQuery(event);
        let nextInput = input.closest('fieldset').siblings('fieldset').find(this.valueTarget);

        let total = this.formatValue(this.getCartTotals());
        let value = this.formatValue(input.val() || total / 2);
        if (value >= total) {
            this.showError('O valor deve ser menor que o total do pedido!');
            input.val('');
            input.change();
            return;
        }
        this.changeValueInput(input, value);
        this.changeValueInput(nextInput, (total - value));
    },
    changeValueInput: function (input, value) {
        input.val(this.formatValue(value, true));
        let fieldset = input.closest('fieldset').first();
        if (pagarmeCard.haveCardForm(fieldset)) {
            pagarmeCard.updateInstallmentsElement(input);
        }
    },
    formatValue: function (value, returnString= false) {
        if (typeof value === 'string') {
            value = parseFloat(value.replace(',', '.'));
        }
        if (returnString) {
            return value.toFixed(2).replace('.', ',');
        }
        return parseFloat(value.toFixed(2));
    },
    showError: function (text) {
        const message = {
            icon: 'error',
            text: text,
            allowOutsideClick: false
        };
        swal.fire(message);
    },
    addEventListener: function () {
        jQuery(this.valueTarget).on('change', function (event){
            pagarmeOrderValue.fillAnotherInput(event)
        });
    },
    start: function () {
        this.addEventListener();
        jQuery(this.firstCardFieldset).find(this.valueTarget).each(function () {
            pagarmeOrderValue.fillAnotherInput(this);
        });
    },
    getCartTotals: function () {
        return cartTotal;
    }
};
pagarmeOrderValue.start();
