(
    function ($) {
        "use strict";
        const script = $('[data-pagarmecheckout-app-id]');
        const appId = script.data('pagarmecheckoutAppId');
        const apiUrl = 'https://api.mundipagg.com/core/v1/tokens';
        const form = $('form.checkout');

        var pagarme = {
            getEndpoint: function () {
                let url = new URL(apiUrl);
                let params = new URLSearchParams(url.search);
                params.append('appId', appId);
                return url + params;
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

        function tokenize() {
            if (pagarme.hasSelectedWallet(this) === false) {
                let endpoint = pagarme.getEndpoint();

                let card = createCheckoutObj(this);
            }
        }

        function createCheckoutObj(fields) {
            var obj = {},
                i = 0,
                length = fields.length,
                prop, key;
            obj['type'] = 'credit_card';
            for (i = 0; i < length; i++) {
                prop = fields[i].getAttribute('data-pagarmecheckout-element');
                if (prop === 'exp_date') {
                    var sep = fields[i].getAttribute('data-pagarmecheckout-separator') ? fields[i].getAttribute('data-pagarmecheckout-separator') : '/';
                    var values = fields[i].value.replace(/\s/g, '').split(sep);
                    obj['exp_month'] = values[0];
                    obj['exp_year'] = values[1];
                } else {
                    key = fields[i].value;
                    if (prop == 'number') {
                        key = key.replace(/\s/g, '');
                    }
                    if (prop == 'brand') {
                        key = fields[i].getAttribute('data-pagarmecheckout-brand');
                    }
                }
                obj[prop] = key;
            }
            return obj;
        }

        function getApiData(url, data, success, fail) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState < 4) {
                    return;
                }
                if (xhr.status == 200) {
                    success.call(null, xhr.responseText);
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
        }

        $("form.checkout").on(
            "checkout_place_order",
            function () {
                execute();
                return false;
            }
        );
    } (jQuery)
);
