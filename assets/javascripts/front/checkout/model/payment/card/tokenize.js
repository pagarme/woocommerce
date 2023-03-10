(
    function ($) {
        "use strict";
        const script = $('[data-pagarmecheckout-app-id]');
        const appId = script.data('pagarmecheckoutAppId');
        const apiUrl = 'https://api.mundipagg.com/core/v1/tokens';
        const token = 'token';
        const vendor = 'pagarme';
        const paymentMethodTarget = 'data-pagarmecheckout-method';
        const sequenceTarget = 'data-pagarmecheckout-card-num';
        const tokenElementTarget = 'data-pagarmecheckout-element';
        const form = $('form.checkout');

        var pagarme = {
            getEndpoint: function () {
                let url = new URL(apiUrl);
                url.searchParams.append('appId', appId);
                return url.toString();
            },
            getCardsForm: function (el) {
                return el.find('fieldset[data-pagarmecheckout="card"]');
            }
        };

        async function execute() {
            let el = pagarmeCard.getCheckoutPaymentElement();
            if (pagarmeCard.isPagarmePayment() && pagarmeCard.haveCardForm(el) !== false) {
                pagarme.getCardsForm(el).each(await tokenize);
            }
        }

        async function tokenize() {
            if (pagarmeCard.hasSelectedWallet(this) === false) {
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
                    ignore = ['brand-input', 'exp_date', 'card-order-value', null],
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
                if (ignore.includes(prop)) {
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

        function showError(text) {
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

        async function createTokenInput(response, field) {
            try {
                let clear = await clearInputTokens(field);
            } catch (e) {
                showError(e.message);
            }
            const objJSON = JSON.parse(response);
            let input = $(document.createElement('input'));
            if (!(field instanceof jQuery)) {
                field = $(field);
            }
            let inputName = vendor + '[' + field.attr(paymentMethodTarget) + '][cards][' + field.attr(sequenceTarget) + '][' + token + ']'
            input.attr('type', 'hidden')
                .attr('name',  inputName)
                .attr('id', inputName)
                .attr('value', objJSON.id)
                .attr(tokenElementTarget, token);
            field.append(input);
        }

        function clearInputTokens(field) {
            return new Promise((resolve) => {
                if (!(field instanceof jQuery)) {
                    field = $(field);
                }
                let inputs = field.find('[' + tokenElementTarget + '=' + token + ']');
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
    }(jQuery)
);
