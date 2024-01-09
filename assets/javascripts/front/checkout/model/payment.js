/* globals wc_pagarme_checkout */

jQuery(function ($) {
    if (typeof wc_pagarme_checkout !== 'undefined') {
        globalThis.wc_pagarme_checkout = wc_pagarme_checkout;
        $.jMaskGlobals.watchDataMask = true;
        wc_pagarme_checkout.validate = function () {
            const checkedPayment = $('form .payment_methods input[name="payment_method"]:checked')?.val();
            if (!checkedPayment) {
                return true;
            }
            const requiredFields = $('#shipping_number:visible, input[data-required=true]:visible,' +
                'select[data-required=true]:visible,' +
                `.wc_payment_method.payment_method_${checkedPayment} [data-pagarmecheckout-element="brand-input"]`);
            let isValid = true;
            requiredFields.each(function (index, item) {
                const field = $(item);
                const wrapper = field.closest( '.form-row' );
                if (field.val() == 0 || !$.trim(field.val())) {
                    field.addClass('invalid').val('');
                    if (isValid) {
                        field.focus();
                    }
                    wrapper.addClass('woocommerce-invalid' ); // error
                    isValid = false;
                }
            });
            if (!isValid) {
                swal('Preencha os campos obrigatórios');
            }
            return isValid;
        }
    }
});
