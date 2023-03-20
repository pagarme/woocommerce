/* globals wc_pagarme_checkout */

jQuery(function ($) {
    globalThis.wc_pagarme_checkout = wc_pagarme_checkout;
    $.jMaskGlobals.watchDataMask = true;
    wc_pagarme_checkout.validate = function () {
        var requiredFields = $('[data-required=true]:visible'),
            isValid = true;

        requiredFields.each(function (index, item) {
            var field = $(item);
            const wrapper = field.closest( '.form-row' )
            if (!$.trim(field.val())) {
                if (field.attr('id') == 'installments') {
                    field = field.next(); //Select2 span
                }
                field.addClass('invalid')
                if (isValid) {
                    field.focus();
                }
                wrapper.addClass('woocommerce-invalid' ); // error
                isValid = false;
            }
        });
        if (!isValid) {
            swal('Prencha os campos obrigatórios');
            $('[data-required=true]:visible.invalid')[0].append('<div className="valid-feedback">Campo Obrigatório</div>');

        }
        return isValid;
    };
});
