/* globals pagarmeCard */
/* jshint esversion: 8 */
let pagarmeTokenize = {
    appId: jQuery('[data-pagarmecheckout-app-id]').data('pagarmecheckoutAppId'),
    apiUrl: 'https://api.pagar.me/core/v5/tokens',
    token: 'token',
    vendor: 'pagarme',
    paymentMethodTarget: 'data-pagarmecheckout-method',
    sequenceTarget: 'data-pagarmecheckout-card-num',
    tokenElementTarget: 'data-pagarme-element',
    getEndpoint: function () {
        let url = new URL(this.apiUrl);
        url.searchParams.append('appId', this.appId);
        return url.toString();
    },
    getCardsForm: function (el) {
        return el.find('fieldset[data-pagarmecheckout="card"]');
    },

    execute: async function () {
        let el = pagarmeCard.getCheckoutPaymentElement();
        if (pagarmeCard.isPagarmePayment() && pagarmeCard.haveCardForm(el) !== false) {
            pagarmeTokenize.getCardsForm(el).each(await pagarmeTokenize.tokenize);
        }
    },

    tokenize: async function () {
        if (pagarmeCard.hasSelectedWallet(this) === false && !pagarmeCard.checkToken(this)) {
            wc_pagarme_checkout.errorTokenize = false;
            let endpoint = pagarmeTokenize.getEndpoint(),
                card = pagarmeTokenize.createCardObject(this),
                field = jQuery(this);
            await pagarmeTokenize.getApiData(
                endpoint,
                card,
                field,
                async function (data) {
                    await pagarmeTokenize.createTokenInput(data, field);
                },
                function (error) {
                    wc_pagarme_checkout.errorTokenize = true;
                    if (error.statusCode == 503) {
                        pagarmeTokenize.showError('Não foi possível gerar uma transação. Serviço indisponível.');
                    } else {
                        pagarmeTokenize.listError(error.errors);
                    }
                }
            );
        }
    },

    createCardObject: function (field) {
        let obj = {};
        jQuery.each(jQuery(field).find('input'), function () {
            let prop = this.getAttribute('data-pagarme-element'),
                ignore = ['brand-input', 'exp_date', 'card-order-value', null],
                value;
            value = this.value;
            if (prop === 'exp_date') {
                let sep = this.getAttribute('data-pagarmecheckout-separator') ? this.getAttribute('data-pagarmecheckout-separator') : '/';
                let values = this.value.replace(/\s/g, '').split(sep);
                obj['exp_month'] = values[0];
                obj['exp_year'] = values[1];
            }
            if ((prop === 'number') || (prop === 'holder_document')) {
                value = this.value.replace(/\D/g, '');
            }
            if (ignore.includes(prop)) {
                return;
            }
            obj[prop] = value;
        });
        return obj;
    },

    getApiData: function (url, data, field, success, fail) {
        return new Promise((resolve) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.onreadystatechange = function () {
                if (xhr.readyState < 4) {
                    return;
                }
                if (xhr.status == 200) {
                    success.call(null, xhr.responseText, field);
                } else {
                    let errorObj = {};
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
    },

    showError: function (text) {
        swal.close();
        const message = {
            icon: 'error',
            html: text,
            allowOutsideClick: false
        };
        swal.fire(message);
    },

    listError: function (errors) {
        let error, rect;
        const element = jQuery('input[name$="payment_method"]:checked').closest('li').find('#wcmp-checkout-errors');

        swal.close();

        wc_pagarme_checkout.errorList = '';

        for (error in errors) {
            (errors[error] || []).forEach(this.parseErrorsList.bind(this, error));
        }

        element.find('.woocommerce-error').html(wc_pagarme_checkout.errorList);
        element.slideDown();

        rect = element.get(0).getBoundingClientRect();

        jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');

        window.scrollTo(0, (rect.top + window.scrollY) - 40);
    },

    parseErrorsList: function (error, message) {
        const translatedError = pagarmeCard.translateErrors(error, message);
        wc_pagarme_checkout.errorList += `<li>${translatedError}<\li>`;
    },

    createTokenInput: async function (response, field) {
        await pagarmeTokenize.clearInputTokens(field);
        const objJSON = JSON.parse(response);
        let input = jQuery(document.createElement('input'));
        if (!(field instanceof jQuery)) {
            field = jQuery(field);
        }
        let inputName = this.vendor + '[' + field.attr(this.paymentMethodTarget) + '][cards][' + field.attr(this.sequenceTarget) + '][' + this.token + ']'
        input.attr('type', 'hidden')
            .attr('name', inputName)
            .attr('id', inputName)
            .attr('value', objJSON.id)
            .attr(this.tokenElementTarget, this.token)
            .attr(pagarmeCard.tokenExpirationAttribute, objJSON.expires_at);
        field.append(input);
    },

    clearInputTokens: function (field) {
        return new Promise((resolve) => {
            if (!(field instanceof jQuery)) {
                field = jQuery(field);
            }
            let inputs = field.find('[' + this.tokenElementTarget + '=' + this.token + ']');
            if (inputs.length) {
                jQuery.each(inputs, function () {
                    this.remove();
                });
            }
            resolve(true);
        });
    }
};

