(
    function ($) {
        "use strict";
        const script = $('[data-pagarmecheckout-app-id]');
        const appId = script.data('pagarmecheckoutAppId');
        const apiUrl = 'https://api.mundipagg.com/core/v1/tokens';
        const token = 'pagarmetoken';
        const form = $('form.checkout');

        var pagarme = {
            isPagarmePayment: function () {
                let value = $('form.checkout input[name="payment_method"]:checked').val();
                return value.indexOf('pagarme');
            },
            getEndpoint: function () {
                let url = new URL(apiUrl);
                url.searchParams.append('appId', appId);
                return  url.toString();
            },
            getCheckoutPaymentElement: function () {
                let value = $('form.checkout input[name="payment_method"]:checked').val();
                return $('.wc_payment_method.payment_method_' + value);
            },
            getCardsForm: function(el) {
                return el.find('fieldset[data-pagarmecheckout="card"]');
            },
            haveCardForm: function (el) {
                if (el.has('fieldset[data-pagarmecheckout="card"]').length) {
                    return true;
                }
                return false;
            },
            hasSelectedWallet: function (el) {
                let elWallet = $(el).find('select[data-element="choose-credit-card"]');
                if (elWallet.length) {
                    return elWallet.val().trim() !== '';
                }
                return false;
            },
        };

        function execute() {
            let el = pagarme.getCheckoutPaymentElement();
            if (pagarme.haveCardForm(el) !== false) {
                pagarme.getCardsForm(el).each(tokenize);
            }
        }

        async function tokenize() {
            if (pagarme.hasSelectedWallet(this) === false) {
                let endpoint = pagarme.getEndpoint(),
                    card = createCardObject(this),
                    field = $(this);
                await getApiData(
                    endpoint,
                    card,
                    field,
                    function (data) {
                        createTokenInput(data, field);
                    },
                    function (error) {
                        showError('Não foi possível gerar a transação segura. Serviço indisponível.')
                    }
                );
            }
        }

        function createCardObject(field) {
            let obj = {};
            $.each($(field).find('input'), function () {
                let prop = this.getAttribute('data-pagarmecheckout-element'),
                    value;
                value = this.value;
                if (prop === 'exp_date') {
                    let sep = this.getAttribute('data-pagarmecheckout-separator') ? this.getAttribute('data-pagarmecheckout-separator') : '/';
                    let values = this.value.replace(/\s/g, '').split(sep);
                    obj['exp_month'] = values[0];
                    obj['exp_year'] = values[1];
                }
                if (prop === 'number') {
                    value = this.value.replace(/\s/g, '');
                }
                if (prop === 'brand-input' || prop === 'exp_date') {
                    return;
                }
                obj[prop] = value;
            });
            return obj;
        }

        function getApiData(url, data, field, success, fail) {
            return new Promise((resolve) => {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url);
                xhr.onreadystatechange = function () {
                    if (xhr.readyState < 4) {
                        return;
                    }
                    if (xhr.status == 200) {
                        success.call(null, xhr.responseText, field);
                    } else {
                        var errorObj = {};
                        if (xhr.response) {
                            errorObj = JSON.parse(xhr.response);
                            errorObj.statusCode = xhr.status;
                        } else {
                            errorObj.statusCode = 503;
                        }
                        fail.call(null, errorObj);
                    }
                };
                xhr.setRequestHeader('Content-Type', 'application/json; charset=utf-8');
                xhr.send(JSON.stringify({
                    card: data
                }));
                return xhr;
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

        async function createTokenInput(response, field)
        {
            try {
               let clear =  await clearInputTokens(field);
            } catch (e) {
                showError(e.message);
            }
            const objJSON = JSON.parse(response);
            let input = $(document.createElement('input'));
            input.attr(
                'type', 'hidden'
            ).attr(
            'name', token
            ).attr(
                'id', token
            ).attr(
                'value', objJSON.id
            );
            if (!(field instanceof jQuery)) {
                field = $(field);
            }
            $(field).append(input);
        }

        function clearInputTokens(field)
        {
            return new Promise((resolve) => {
                if (!(field instanceof jQuery)) {
                    field = $(field);
                }
                let inputs = field.find('#' + token);
                if (inputs.length) {
                    $.each(inputs, function () {
                        this.remove();
                    });
                }
                resolve(true);
            });
        }

        $("form.checkout").on(
            "checkout_place_order",
            function () {
                try {
                    execute();
                } catch (e) {
                    return false;
                }
            }
        );
    } (jQuery)
);
