/* globals wc_pagarme_checkout */

(function ($) {
    const cardValueTarget = 'input[data-element=card-order-value]';

    let pagarme = {
        getCheckoutPaymentElement: function () {
            let value = $('form.checkout input[name="payment_method"]:checked').val();
            return $('.wc_payment_method.payment_method_' + value);
        },
        isPagarmePayment: function () {
            return $('form.checkout input[name="payment_method"]:checked').val().indexOf('pagarme');
        },
        keyEventHandler: function (e) {
            fillAnotherInput(e);
        },
        getCartTotals: function () {
            return cartTotal;
        }
    }

    async function fillAnotherInput(e) {
        let input = $(e.currentTarget);
        const empty = '';
        let nextInput = input.closest('fieldset').siblings('fieldset').find('input').filter(':visible:first');
        if (nextInput.length === 0) {
            nextInput = input.closest('div').siblings('div').find('input').first();
        }
        let value = await formatValue(e.currentTarget.value);
        if (!value) {
            return;
        }
        let total = await formatValue(pagarme.getCartTotals());
        if (value > total) {
            showError('O valor não pode ser maior que total do pedido!');
            input.val(empty);
            nextInput.val(empty);
            return;
        }
        nextInput.val(await formatValue((total - value), false));
        input.val(await formatValue(value, false));
    }

    function formatValue(value, raw = true) {
        return new Promise((resolve) => {
            if (raw) {
                if (typeof value !== 'string') {
                    value = value.toString();
                }
                value = value.replace('.', '');
                resolve(parseFloat(value.replace(',', '.')));
            } else {
                if (typeof value === 'string') {
                    value = parseFloat(value);
                }
                resolve(value.toFixed(2).replace('.', ','));
            }
        });
    }

    function addsMask() {
        $(cardValueTarget).mask('#.##0,00', {
            reverse: true
        });
    }

    function showError(text)
    {
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
    }

    $( document ).ready(function() {
        addsMask();
    });

    $(cardValueTarget).on('blur', function (e) {
        pagarme.keyEventHandler(e);
    });

    } (jQuery)
);
