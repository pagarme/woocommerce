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
            this.addsMask();
            this.addEventListener();
        },
        addsMask: function () {
            // $('.pagarme-card-form-card-number').mask('0000 0000 0000 0000');
            // $('.pagarme-card-form-card-expiry').mask('00 / 00');
            // $('.pagarme-card-form-card-cvc').mask('0000');
            // $('input[name*=\\[cpf\\]]').mask('000.000.000-00');
            // $('input[name*=\\[zip_code\\]]').mask('00000-000');
            // $('#billing_cpf').change(function () {
            //     $('input[name="pagarme[voucher][cards][1][document-holder]"]').empty();
            //     $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
            // });
            // $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
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
