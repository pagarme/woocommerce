jQuery(function ($) {
    const multiCustomerTarget = 'input[data-element=enable-multicustomers]';
    const form = 'fieldset[data-pagarme-payment-element=multicustomers]'
    window.pagarmeMultiCustomer = {
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
            this.addEventListener();
        },
        addEventListener: function () {
            $(multiCustomerTarget).click(function (e) {
                let input = $(e.currentTarget);
                let method = input.is(':checked') ? 'slideDown' : 'slideUp';
                if (input.parent().closest('fieldset').find(form).length) {
                    input.parent().closest('fieldset').find(form)[method]();
                } else {
                    input.parent().closest('div').find(form)[method]();
                }
            });
        },
    }
});
