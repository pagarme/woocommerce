jQuery(function ($) {
    const multiCustomerTarget = 'input[data-element=enable-multicustomers]';
    const form = 'fieldset[data-pagarme-payment-element=multicustomers]'
    window.pagarmeMultiCustomer = {
        start: function () {
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
