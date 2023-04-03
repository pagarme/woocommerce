/* globals wc_pagarme_checkout */

jQuery(function ($) {
    if (typeof wc_pagarme_checkout !== 'undefined') {
        globalThis.wc_pagarme_checkout = wc_pagarme_checkout;
        $.jMaskGlobals.watchDataMask = true;
        wc_pagarme_checkout.validate = function () {
            var requiredFields = $('#billing_number, #shipping_number:visible, input[data-required=true]:visible'),
                isValid = true;
            requiredFields.each(function (index, item) {
                var field = $(item);
                const wrapper = field.closest( '.form-row' )
                if (field.val() == 0 || !$.trim(field.val())) {
                    if (field.attr('id') == 'installments') {
                        field = field.next(); //Select2 span
                    }
                    field.addClass('invalid').val('');
                    if (isValid) {
                        field.focus();
                    }
                    wrapper.addClass('woocommerce-invalid' ); // error
                    isValid = false;
                }
            });
            if (!isValid) {
                swal('Prencha os campos obrigatórios');
            }
            return isValid;
        }
    }
});
