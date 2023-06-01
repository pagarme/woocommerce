/* globals wc_pagarme_checkout */
$ = jQuery;

const cardNumberTarget = 'input[data-element="pagarme-card-number"]';
const brandTarget = '[data-pagarmecheckout-element="brand-input"]';
const brandImgTarget = 'span[class="pagarme-brand-image"]';
const valueTarget = '[data-pagarmecheckout-element="order-value"]';
const installmentsTarget = '[data-pagarme-component="installments"]';
const mundiCdn = 'https://cdn.mundipagg.com/assets/images/logos/brands/png/';
const tokenElement = '[data-pagarmecheckout-element="token"]';
let cardsMethods = [];
let brands = [];

let pagarmeCard = {
    limitTokenize: 10,
    canSubmit: false,
    haveCardForm: function (e) {
        let elem = null;
        if (e instanceof $) {
            elem = e;
        }
        if (e instanceof $.Event) {
            elem = $(e.currentTarget);
        }
        if (!elem) {
            throw "Cant check card form: Invalid element received";
        }
        if (elem.is("fieldset") && elem.attr("data-pagarmecheckout") === 'card') {
            return true;
        }
        if (elem.has('fieldset[data-pagarmecheckout="card"]').length) {
            return true;
        }
        return false;
    },
    getCheckoutPaymentElement: function () {
        let value = $('form.checkout input[name="payment_method"]:checked').val();
        return $('.wc_payment_method.payment_method_' + value);
    },
    isPagarmePayment: function () {
        return $('form.checkout input[name="payment_method"]:checked').val().indexOf('pagarme');
    },
    keyEventHandlerCard: function (e) {
        this.loadBrand(e);
    },
    formHandler: function () {
        if (this.isPagarmePayment()) {
            let e = this.getCheckoutPaymentElement();
            let cardsForm = e.find('fieldset[data-pagarmecheckout="card"]');
            return this.checkTokenCard(cardsForm);
        }
        return true;
    },
    hasSelectedWallet: function (el) {
        let elWallet = $(el).find('select[data-element="choose-credit-card"]');
        if (elWallet.length) {
            return elWallet.val().trim() !== '';
        }
        return false;
    },

    checkTokenCard: function (e) {
        let allResult = [];
        e.each(async function () {
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

    getBrandTarget: function () {
        return brandTarget;
    },

    checkToken: function (e, step = 0) {
        if (!(e instanceof jQuery)) {
            e = $(e);
        }
        return !!e.find(tokenElement).length;
    },

    getCardDataContingency: async function (cardNumber) {
        let oldPrefix = '',
            types = await this.getBrands(true),
            bin = cardNumber.substring(0, 6),
            currentBrand,
            data;
        for (let i = 0; i < types.length; i += 1) {
            let current_type = types[i];
            for (let j = 0; j < current_type.prefixes.length; j += 1) {
                let prefix = current_type.prefixes[j].toString();
                if (bin.indexOf(prefix) === 0 && oldPrefix.length < prefix.length) {
                    oldPrefix = prefix;
                    currentBrand = current_type.brand;
                    data = current_type;
                }
            }
        }
        return data;
    },

    getBrands: function (onlyBrands = false) {
        return new Promise((resolve) => {
            if (onlyBrands) {
                let types = [];
                cardsMethods.forEach(function (key) {
                    $.each(wc_pagarme_checkout.config.payment[key].brands, function () {
                        types.push(this);
                    });
                });
                resolve(types);
            }
            cardsMethods.forEach(function (key) {
                brands[key] = wc_pagarme_checkout.config.payment[key].brands;
            });
        });
    },

    getCardsMethods: function () {
        $.each(wc_pagarme_checkout.config.payment, function (method) {
            if (wc_pagarme_checkout.config.payment[method].is_card) {
                cardsMethods.push(method);
            }
        });
    },

    loadBrand: async function (e) {
        let elem = e.currentTarget;
        if (!this.isVisible(elem)) {
            return;
        }
        let cardNumber = elem.value.replace(/\s/g, '');
        if (cardNumber.length < 6) {
            return;
        }
        let card = await this.getCardData(cardNumber);
        this.changeBrand(e, card);
        this.updateInstallmentsElement(e);
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
        let codeWithArray = {
            name: 'CVV',
            size: value.cvv
        };
        value = {
            title: value.brandName,
            type: value.brandName,
            gaps: value.gaps,
            lengths: value.lenghts,
            image: value.brandImage,
            mask: value.mask,
            size: value.size,
            brand: value.brand,
            possibleBrands: value.possibleBrands,
            code: codeWithArray
        };
        result.push($.extend(true, {}, value));
        return result;
    },

    getCardDataByApi: function (cardNumber) {
        return new Promise((resolve) => {
            let bin = cardNumber.substring(0, 6);
            $.ajax({
                type: "GET",
                dataType: "json",
                url: 'https://api.mundipagg.com/bin/v1/' + bin,
                async: false,
                cache: false,
                success: function (data) {
                    resolve(data);
                },
                error: function (xhr, textStatus, errorThrown) {
                    resolve(textStatus);
                }
            });
        });
    },

    changeBrand: function (e, card) {
        if (typeof e == 'undefined' || typeof card == 'undefined') {
            throw "Invalid data to change card brand";
        }
        let elem = e.currentTarget;
        let imageSrc = this.getImageSrc(card);
        let imgElem = $(elem).parent().find('img');
        $(elem).parents('.pagarme-card-number-row').find(brandTarget).attr('value', card[0].brand);
        if (imgElem.length) {
            imgElem.attr('src', imageSrc);
        } else {
            let img = $(document.createElement('img'));
            $(elem).parent().append(
                img.attr(
                    'src', imageSrc
                )
            );
        }
    },

    getImageSrc: function (card) {
        if (card[0].image) {
            return card[0].image;
        }
        return mundiCdn + card[0].brand + '.png';
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

    updateInstallmentsElement: function (e) {
        let elem = null;
        if (e instanceof $) {
            elem = e;
        }
        if (e instanceof $.Event) {
            elem = $(e.currentTarget);
        }
        if (!elem) {
            return false;
        }
        let brand = elem.closest('fieldset').find(brandTarget).val();
        let total = elem.closest('fieldset').find(valueTarget).val();
        if (total) {
            total = pagarmeCard.formatValue(total);
        }
        let cardForm = elem.closest("fieldset");
        let select = cardForm.find(installmentsTarget);
        if (select.data('type') == '1' && elem.data('pagarmecheckout-element') != 'order-value') {
            return;
        }
        if (!total)
            total = cartTotal;
        if ((!total) || (!brand && select.data("type") == 2))
            return false;
        let storageName = btoa(brand + total);
        sessionStorage.removeItem(storageName);
        let storage = sessionStorage.getItem(storageName);
        if (storage) {
            select.html(storage);
        } else {
            let ajax = $.ajax({
                'url': ajaxUrl,
                'data': {
                    'action': 'xqRhBHJ5sW',
                    'flag': brand,
                    'total': total
                }
            });
            ajax.done(function (response) {
                pagarmeCard._done(select, storageName, cardForm, response);
            });
            ajax.fail(function () {
                pagarmeCard._fail(cardForm);
            });
            pagarmeCard.showLoader(cardForm);
        }
    },

    _done: function (select, storageName, e, response) {
        select.html(response);
        sessionStorage.setItem(storageName, response);
        this.removeLoader(e);
    },

    _fail: function (e) {
        this.removeLoader(e);
    },

    removeLoader: function (e) {
        if (!(e instanceof jQuery)) {
            e = $(e);
        }
        e.unblock();
    },

    showLoader: function (e) {
        if (!(e instanceof jQuery)) {
            e = $(e);
        }
        e.block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });
    },

    showError: function (text) {
        const message = {
            type: 'error',
            html: text,
            allowOutsideClick: false
        };
        swal(message);
    },
    execute: async function () {
        let result = pagarmeCard.formHandler(),
            i = 1;
        try {
            while (!result && i <= this.limitTokenize) {
                if (i === this.limit) {
                    this.removeLoader(this.getCheckoutPaymentElement());
                    throw "Tokenize timeout";
                }
                if (wc_pagarme_checkout.errorTokenize === true) {
                    this.removeLoader(this.getCheckoutPaymentElement());
                    return;
                }
                await pagarmeCard.wait();
                result = pagarmeCard.formHandler();
                i++;
            }

            this.canSubmit = true;
            $("form.checkout, form#order_review").submit();
        } catch (er) {
            if (typeof er === 'string') {
                this.showError(er);
            } else {
                this.showError(er.message);
            }
        }
    },
    canExecute: function(e) {
        e.preventDefault();
        if (!wc_pagarme_checkout.validate() || wc_pagarme_checkout.errorTokenize === true) {
            return false;
        }
        let el = pagarmeCard.getCheckoutPaymentElement();
        if (pagarmeCard.isPagarmePayment() && !pagarmeCard.canSubmit &&
            pagarmeCard.haveCardForm(el) && !pagarmeCard.hasSelectedWallet(el)
        ) {
            pagarmeCard.execute();
            return false;
        }
        return true;
    },
    addEventListener: function (paymentTarget) {
        $(paymentTarget + ' ' + cardNumberTarget).on('change', function (e) {
            pagarmeCard.keyEventHandlerCard(e);
        });
        $("form.checkout").on(
            "checkout_place_order",
            function (e) {
                return pagarmeCard.canExecute(e);
            }
        );
        $('#billing_cpf').change(function () {
            $('input[name="pagarme[voucher][cards][1][document-holder]"]').empty();
            $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).trigger('input');
        });
        $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).trigger('input');

    },
    start: function (paymentTarget) {
        this.getCardsMethods();
        this.getBrands();
        this.addEventListener(paymentTarget);
        if (typeof pagarmeCheckoutWallet == 'object') {
            pagarmeCheckoutWallet.start(paymentTarget);
        }

    },
};
