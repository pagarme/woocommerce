const cardValueTarget = 'input[data-pagarmecheckout-element="order-value"]';
const firstCardValue = '[data-pagarmecheckout-card-num="1"]';

let pagarmeOrderValue = {
    started: false,
    isStarted: function () {
        if (!this.started) {
            this.started = true;
            return false;
        }
        return true;
    },
    start: function () {
        // if (this.isStarted()) {
        //     return;
        // }
        this.addEventListener();
    },
    fillAnotherInput: async function (e) {
        let input = jQuery(e.currentTarget);
        const empty = '';
        let nextInput = input.closest('fieldset').siblings('fieldset').find('input').filter(':visible:first');
        if (nextInput.length === 0) {
            nextInput = input.closest('div').siblings('div').find('input').first();
        }
        let total = this.formatValue(this.getCartTotals());
        let value = this.formatValue(e.currentTarget.value);
        if (!value) {
            value = this.formatValue(this.getCartTotals() / 2);
        }
        if (value > total) {
            this.showError('O valor não pode ser maior que total do pedido!');
            input.val(empty);
            nextInput.val(empty);
            return;
        }
        nextInput.val(this.formatValue((total - value), false));
        input.val(this.formatValue(value, false));
        [e, nextInput].forEach(function (input) {
            if (!input instanceof $) {
                input = $(input);
            }
            if (input instanceof $.Event) {
                input = $(input.currentTarget);
            }
            let fieldset = input.closest('fieldset').first();
            if (pagarmeCard.haveCardForm(fieldset)) {
                pagarmeCard.updateInstallmentsElement(fieldset);
            }
        });
    },
    formatValue: function (value, raw = true) {
        if (raw) {
            if (typeof value !== 'string') {
                value = value.toString();
            }
            return parseFloat(value.replace(',', '.'));
        }
        if (typeof value === 'string') {
            value = parseFloat(value);
        }
        return value.toFixed(2).replace('.', ',');
    },
    showError: function (text) {
        const message = {
            type: 'error',
            html: text,
            allowOutsideClick: false
        };
        try {
            swal(message);
        } catch (e) {
            new swal(message);
        }
    },
    addEventListener: function () {
        jQuery(cardValueTarget).on('change', function (e) {
            pagarmeOrderValue.keyEventHandler(e);
        });
    },
    keyEventHandler: function (e) {
        this.fillAnotherInput(e);
        pagarmeCard.updateInstallmentsElement(e);
    },
    getCartTotals: function () {
        return cartTotal;
    }
}
