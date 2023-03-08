/* globals wc_pagarme_checkout */

jQuery(function ($) {
    globalThis.wc_pagarme_checkout = wc_pagarme_checkout;
    wc_pagarme_checkout.validate = function () {
        var requiredFields = $('[data-required=true]:visible'),
            isValid = true;

        requiredFields.each(function (index, item) {
            var field = $(item);
            if (!$.trim(field.val())) {
                if (field.attr('id') == 'installments') {
                    field = field.next(); //Select2 span
                }
                field.addClass('invalid');
                isValid = false;
            }
        });

        return isValid;
    };

});
