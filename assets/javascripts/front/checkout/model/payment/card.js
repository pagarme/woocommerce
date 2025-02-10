/* globals wc_pagarme_checkout */
/* jshint esversion: 11 */
let pagarmeCard = {
    limitTokenize: 10,
    tokenExpirationAttribute: 'data-pagarmecheckout-expiration',
    cardNumberTarget: 'input[data-element="pagarme-card-number"]',
    cardHolderNameTarget: 'input[data-element="card-holder-name"]',
    cardExpiryTarget: 'input[data-element="card-expiry"]',
    cardCvvTarget: 'input[data-element="card-cvv"]',
    brandTarget: 'input[data-pagarme-element="brand-input"]',
    valueTarget: 'input[data-pagarme-element="order-value"]',
    installmentsTarget: '[data-pagarme-component="installments"]',
    installmentsInfoTarget: '[data-pagarme-component="installments-info"]',
    maskTargets: 'input[class*="pagarme-card-form"][data-mask]',
    mundiCdn: 'https://cdn.mundipagg.com/assets/images/logos/brands/png/',
    tokenElement: '[data-pagarme-element="token"]',
    fieldsetCardElements: 'fieldset[data-pagarmecheckout="card"]',
    billingCpfId: '#billing_cpf',
    voucherDocumentHolder: 'input[name="pagarme[voucher][cards][1][document-holder]"]',
    formatEventToJQuery: function (event) {
        if (event instanceof jQuery.Event) {
            return jQuery(event.currentTarget);
        }
        if (!(event instanceof jQuery)) {
            return jQuery(event);
        }
        return event;
    },
    haveCardForm: function (event) {
        let elem = this.formatEventToJQuery(event);
        if (!elem) {
            throw new Error("Can't check card form: Invalid element received");
        }
        if (elem.is("fieldset") && elem.attr("data-pagarmecheckout") === 'card') {
            return true;
        }
        return !!elem.has(this.fieldsetCardElements).length;
    },
    getCheckoutPaymentElement: function () {
        const value = jQuery('form .payment_methods input[name="payment_method"]:checked').val();
        return jQuery('.wc_payment_method.payment_method_' + value);
    },
    isPagarmePayment: function () {
        let paymentSelected = jQuery('form .payment_methods input[name="payment_method"]:checked');
        if(paymentSelected.length <= 0) {
            return false;
        }
        paymentSelected = paymentSelected.val();
        if(!paymentSelected) {
            return false;
        }
        if(paymentSelected.indexOf('pagarme') == '-1') {
            return false;
        }
        return paymentSelected.indexOf('pagarme');
    },
    preventSpecialCharacter: function (element) {
        let selectionStart = element.selectionStart;
        const regex = /[^a-z ]/gi;
        const val = jQuery(element).val();

        if(regex.test(val)) {
            jQuery(element).val(val.replace(regex, ''));
            selectionStart--;
        }

        element.setSelectionRange(selectionStart, selectionStart);
    },
    keyEventHandlerCard: function (event) {
        this.clearToken(event);
        this.loadBrand(event);
    },
    clearErrorMessages: function () {
        jQuery('input[name$="payment_method"]:checked')
            .closest('li')
            .find('#wcmp-checkout-errors')
            .hide();
        wc_pagarme_checkout.errorTokenize = false;
    },
    clearToken: function (event) {
        const token = this.formatEventToJQuery(event).closest(this.fieldsetCardElements)
            .find(this.tokenElement);
        jQuery(token).remove();
    },
    isTokenized: function () {
        if (this.isPagarmePayment()) {
            const checkoutPaymentElement = this.getCheckoutPaymentElement();
            const cardsForm = checkoutPaymentElement.find(this.fieldsetCardElements);
            return this.checkTokenCard(cardsForm);
        }
        return true;
    },
    hasSelectedWallet: function (el) {
        let elWallet = jQuery(el).find('select[data-element="choose-credit-card"]');
        if (elWallet.length) {
            return elWallet.val().trim() !== '';
        }
        return false;
    },
    checkTokenCard: function (event) {
        let allResult = [];
        event.each(function () {
            if (pagarmeCard.hasSelectedWallet(this)) {
                allResult.push(true);
                return;
            }
            allResult.push(pagarmeCard.checkToken(this));
        });
        return !allResult.includes(false);
    },

    wait: async function (ms = 1000) {
        return new Promise(resolve => {
            setTimeout(resolve, ms);
        });
    },
    checkToken: function (event) {
        event = this.formatEventToJQuery(event);
        return !!event.find(this.tokenElement).length;
    },
    getCardDataContingency: async function (cardNumber) {
        let oldPrefix = '',
            types = this.getBrands(),
            bin = cardNumber.substring(0, 6),
            data;
        for (const currentType of types) {
            for (const prefix of currentType.prefixes) {
                const prefixText = prefix.toString();
                if (bin.indexOf(prefixText) === 0 && oldPrefix.length < prefixText.length) {
                    oldPrefix = prefixText;
                    data = currentType;
                }
            }
        }
        return data;
    },
    getBrands: function () {
        let types = [];
        let cardsMethods = this.getCardsMethods();
        cardsMethods.forEach(function (key) {
            jQuery.each(wc_pagarme_checkout.config.payment[key].brands, function () {
                types.push(this);
            });
        });
        return types;
    },
    getCardsMethods: function () {
        let cardsMethods = [];
        jQuery.each(wc_pagarme_checkout.config.payment, function (method) {
            if (wc_pagarme_checkout.config.payment[method].is_card) {
                cardsMethods.push(method);
            }
        });
        return cardsMethods;
    },
    loadBrand: async function (event) {
        let elem = event.target;
        this.removeBrand(elem);
        if (!this.isVisible(elem)) {
            return;
        }
        let cardNumber = elem.value.replace(/\s/g, '');
        if (cardNumber.length < 6) {
            return;
        }
        try {
            let card = await this.getCardData(cardNumber);
            this.changeBrand(elem, card);
            this.updateInstallmentsElement(elem);
        } catch (exception) {
            this.showError(exception.message);
        }
    },
    removeBrand: function (elem) {
        const imgElem = jQuery(elem).parent().find('img');
        imgElem.remove();
        jQuery(elem).parents('.pagarme-card-number-row').find(this.brandTarget).val('');
    },
    isVisible: function (obj) {
        return obj.offsetWidth > 0 && obj.offsetHeight > 0;
    },
    getCardData: async function (cardNumber) {
        let result = [];
        let value = await this.getCardDataByApi(cardNumber);
        if (value === 'error' || typeof value.brandName == 'undefined') {
            value = await this.getCardDataContingency(cardNumber);
        }
        if (typeof value === 'undefined') {
            return undefined;
        }
        let codeWithArray = {
            name: 'CVV',
            size: value.cvv
        };
        value = {
            title: value.brandName,
            type: value.brandName,
            gaps: value.gaps,
            lengths: value.lengths,
            image: value.brandImage,
            mask: value.mask,
            size: value.size,
            brand: value.brand,
            possibleBrands: value.possibleBrands,
            code: codeWithArray
        };
        result.push(jQuery.extend(true, {}, value));
        return result;
    },
    getCardDataByApi: function (cardNumber) {
        return new Promise((resolve) => {
            let bin = cardNumber.substring(0, 6);
            jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: 'https://api.pagar.me/bin/v1/' + bin,
                async: false,
                cache: false,
                success: function (data) {
                    resolve(data);
                },
                error: function (xhr, textStatus) {
                    resolve(textStatus);
                }
            });
        });
    },
    changeBrand: function (elem, card) {
        if (typeof card == 'undefined') {
            throw new Error("Invalid data to change card brand");
        }

        let imageSrc = this.getImageSrc(card);
        let imgElem = jQuery(elem).parent().find('img');

        const doesNotHaveBrand = !card[0].brand || card[0]?.brand?.length === 0;
        if (doesNotHaveBrand) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    'invalidBrand'
                ]
            );
            return;
        }

        jQuery(elem).parents('.pagarme-card-number-row').find(this.brandTarget).attr('value', card[0].brand);
        if (imgElem.length) {
            imgElem.attr('src', imageSrc);
        } else {
            let img = jQuery(document.createElement('img'));
            jQuery(elem).parent().append(
                img.attr(
                    'src', imageSrc
                )
            );
        }
    },
    showErrorInPaymentMethod: function (error) {
        const element = jQuery('input[name$="payment_method"]:checked')
            .closest("li")
            .find("#wcmp-checkout-errors");

        swal.close();

        wc_pagarme_checkout.errorList = `<li>${error}<\li>`;

        element.find(".woocommerce-error").html(wc_pagarme_checkout.errorList);
        element.slideDown();

        const rect = element.get(0).getBoundingClientRect();

        jQuery("#wcmp-submit").removeAttr("disabled", "disabled");

        window.scrollTo(0, rect.top + window.scrollY - 40);
    },
    getImageSrc: function (card) {
        if (card[0].image) {
            return card[0].image;
        }
        return this.mundiCdn + card[0].brand + '.png';
    },
    formatValue: function (value, raw = true) {
        if (raw) {
            if (typeof value !== 'string') {
                value = value.toString();
            }
            return parseFloat(value.replace(',', '.'));
        }
        if (typeof value === 'string') {
            value = parseFloat(value);
        }
        return value.toFixed(2).replace('.', ',');
    },
    updateInstallmentsElement: function (eventElement) {
        const elem = this.formatEventToJQuery(eventElement);
        if (!elem) {
            return false;
        }
        const brand = elem.closest('fieldset').find(this.brandTarget).val();
        let total = elem.closest('fieldset').find(this.valueTarget).val();
        if (total) {
            total = pagarmeCard.formatValue(total);
        }
        const cardForm = elem.closest("form");
        const cardFieldset = elem.closest("fieldset");
        const select = cardFieldset.find(this.installmentsTarget);
        const info = cardFieldset.find(this.installmentsInfoTarget);
        if (!total) {
            total = cartTotal;
        }
        if ((!total) ||
            (select.data("type") === 2 && !brand) ||
            (select.data("type") === 1 && elem.data('element') !== "order-value")
        ) {
            return false;
        }
        const storageName = btoa(brand + total);
        sessionStorage.removeItem(storageName);
        const storage = sessionStorage.getItem(storageName);
        if (storage) {
            select.html(storage);
        } else {
            let ajax = jQuery.ajax({
                'url': ajaxUrl,
                'data': {
                    'action': 'xqRhBHJ5sW',
                    'flag': brand,
                    'total': total
                }
            });
            ajax.done(function (response) {
                pagarmeCard._done(select, info, storageName, cardForm, JSON.parse(response));
            });
            ajax.fail(function () {
                pagarmeCard._fail(cardForm);
            });
            pagarmeCard.showLoader(cardForm);
        }
        return true;
    },

    _done: function (select, info, storageName, event, response) {
        if (info.length) {
            info.addClass('pagarme-hidden');
            if(response.installmentsConfig > 1) {
                info.removeClass('pagarme-hidden');
            }
        }
        select.html(response.optionsHtml);
        sessionStorage.setItem(storageName, response);
        this.removeLoader(event);
    },
    _fail: function (event) {
        this.removeLoader(event);
    },
    removeLoader: function (event) {
        const formattedEvent = this.formatEventToJQuery(event);

        if (typeof formattedEvent.unblock === 'function') {
            formattedEvent.unblock();
           return;
        }

        if (typeof jQuery.unblockUI === 'function') {
            jQuery.unblockUI();
        }
    },
    showLoader: function (event) {
        const formattedEvent = this.formatEventToJQuery(event);

        if (typeof formattedEvent.block === 'function') {
            formattedEvent.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
           return;
        }

        if (typeof jQuery.blockUI === 'function') {
            jQuery.blockUI({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });
        }
    },
    showError: function (text) {
        const errorMessageText = this.translateErrors('card', text);
        const message = {
            icon: 'error',
            html: errorMessageText,
            allowOutsideClick: false
        };
        swal.fire(message);
    },
    translateErrors: function (error, message) {
        error = error.replace('request.', '');
        const output = `${error}: ${message}`;
        const ptBrMessages = PagarmeGlobalVars.checkoutErrors.pt_BR;
        if (ptBrMessages.hasOwnProperty(output)) {
            return ptBrMessages[output];
        }
        return output;
    },
    execute: async function (event) {
        this.showLoader(event);
        try {
            for (let i = 1; !pagarmeCard.isTokenized() && i <= this.limitTokenize; i++) {
                if (i === this.limitTokenize) {
                    this.removeLoader(event);
                    throw new Error("Tokenize timeout");
                }
                if (wc_pagarme_checkout.errorTokenize === true) {
                    this.removeLoader(event);
                    return;
                }
                await pagarmeCard.wait();
            }
            let formCheckout = this.formatEventToJQuery(event);
            formCheckout.submit();
            formCheckout.find(pagarmeCard.tokenElement).remove();
        } catch (er) {
            this.removeLoader(event);
            if (typeof er === 'string') {
                this.showError(er);
            } else {
                this.showError(er.message);
            }
        }
    },
    canExecute: function (event) {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();

        const cardBrand = checkoutPaymentElement.parents('.pagarme-card-number-row')
            .find(this.brandTarget);
        if (cardBrand?.val()?.length === 0 || wc_pagarme_checkout.errorTokenize === true) {
            return false;
        }

        if (pagarmeCard.isPagarmePayment() &&
            !pagarmeCard.isTokenized() &&
            pagarmeCard.haveCardForm(checkoutPaymentElement)
        ) {
            if (typeof pagarmeTds == 'object') {
                const tdsExecuted = pagarmeTds.start(event);

                if (tdsExecuted) {
                    return false;
                }
            }
            this.executeAll(event);
            return false;
        }
        return true;
    },
    executeAll: function (event) {
        pagarmeTokenize.execute();
        pagarmeCard.execute(event);
    },
    brandIsVisaOrMaster: function() {
        const checkoutPaymentElement = this.getCheckoutPaymentElement();
        const brand = jQuery(checkoutPaymentElement).find(this.brandTarget)
            .val();
        return brand === "visa" || brand === "mastercard";
    },
    onChangeBillingCpf: function () {
        let cpf = jQuery(this.billingCpfId).val();
        jQuery(this.voucherDocumentHolder).empty();
        jQuery(this.voucherDocumentHolder).val(cpf);
    },
    addEventListener: function () {
        jQuery(document.body).on('updated_checkout', function () {
            pagarmeCard.renewEventListener();
            jQuery.applyDataMask(pagarmeCard.maskTargets);
            let creditCardField = jQuery(pagarmeCard.cardNumberTarget);
            creditCardField.each(function () {
                if (jQuery(this)?.val() && pagarmeCard.isVisible(jQuery(this)[0])) {
                    jQuery(this).change();
                }
            });
        });

        jQuery(document).ready(function () {
            jQuery('form.checkout').on('checkout_place_order', function (event) {
                return pagarmeCard.canExecute(event);
            });
            jQuery('form#order_review').on('submit', function (event) {
                return pagarmeCard.canExecute(event);
            });
        });

        jQuery(this.billingCpfId).on('change', function () {
            pagarmeCard.onChangeBillingCpf();
        });

        jQuery(this.cardHolderNameTarget).on('input', function () {
            pagarmeCard.preventSpecialCharacter(this);
        });

        jQuery(this.cardNumberTarget).on('change', function (event) {
            pagarmeCard.keyEventHandlerCard(event);
        });

        jQuery(`${this.fieldsetCardElements} input`).on('change', function () {
            pagarmeCard.clearErrorMessages();
        });
    },
    renewEventListener: function () {
        jQuery(this.cardNumberTarget).on('change', function (event) {
            pagarmeCard.keyEventHandlerCard(event);
        });
        jQuery(`${this.fieldsetCardElements} input`).on('change', function () {
            pagarmeCard.clearErrorMessages();
        });
        if (typeof pagarmeCheckoutWallet == 'object') {
            pagarmeCheckoutWallet.addEventListener();
        }
        if (typeof pagarmeOrderValue == 'object') {
            pagarmeOrderValue.start();
        }

        jQuery(this.cardHolderNameTarget).on('input', function () {
            pagarmeCard.preventSpecialCharacter(this);
        });
    },
    start: function () {
        jQuery(document).ready(function () {
            jQuery.jMaskGlobals.watchDataMask = true;
        });
        this.getCardsMethods();
        this.addEventListener();
        this.onChangeBillingCpf();
    }
};
pagarmeCard.start();
