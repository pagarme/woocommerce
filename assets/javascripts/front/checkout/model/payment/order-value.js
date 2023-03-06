const cardValueTarget = 'input[data-pagarmecheckout-element="order-value"]';
const firstCardValue = '[data-pagarmecheckout-card-num="1"]';
let pagarmeOrderValue = {
    started: false,
    isStarted: function (){
        if (!this.started){
            this.started = true;
            return false;
        }
        return true;
    },
    start: function () {
        if (this.isStarted()) {
            return;
        }
        this.addsMask();
        this.addEventListener();
    },
    fillAnotherInput: async function (e) {
        let input = jQuery(e.currentTarget);
        const empty = '';
        let nextInput = input.closest('fieldset').siblings('fieldset').find('input').filter(':visible:first');
        if (nextInput.length === 0) {
            nextInput = input.closest('div').siblings('div').find('input').first();
        }
        let total = await this.formatValue(this.getCartTotals());
        let value = await this.formatValue(e.currentTarget.value);
        if (!value) {
            value = await this.formatValue(this.getCartTotals() / 2);
        }
        if (value > total) {
            this.showError('O valor não pode ser maior que total do pedido!');
            input.val(empty);
            nextInput.val(empty);
            return;
        }
        nextInput.val(await this.formatValue((total - value), false));
        input.val(await this.formatValue(value, false));
        pagarmeCard.updateInstallmentsElement(e);
    },
    formatValue: function (value, raw = true) {
        return new Promise((resolve) => {
            if (raw) {
                if (typeof value !== 'string') {
                    value = value.toString();
                }
                resolve(parseFloat(value.replace(',', '.')));
            } else {
                if (typeof value === 'string') {
                    value = parseFloat(value);
                }
                resolve(value.toFixed(2).replace('.', ','));
            }
        });
    },
    addsMask: function () {
        jQuery(this.cardValueTarget).mask('#.##0,00', {
            reverse: true
        });
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
        jQuery(this.cardValueTarget).on('change', function (e) {
            pagarmeOrderValue.keyEventHandler(e);
        });
    },
    keyEventHandler: function (e) {
        this.fillAnotherInput(e);
    },
    getCartTotals: function () {
        return cartTotal;
    }
}
