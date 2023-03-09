/* globals wc_pagarme_checkout */
$ = jQuery;

const cardNumberTarget = 'input[data-element="pagarme-card-number"]';
const brandTarget = '[data-pagarmecheckout-element="brand-input"]';
const brandImgTarget = 'span[name="brand-image"]';
const valueTarget = '[data-pagarmecheckout-element="order-value"]';
const installmentsTarget = '[data-pagarme-component="installments"]';
const mundiCdn = 'https://cdn.mundipagg.com/assets/images/logos/brands/png/';
const tokenElement = '[data-pagarmecheckout-element="token"]';
let cardsMethods = [];
let brands = [];

let pagarmeCard = {
    limit: 10,
    canSubmit: false,
    started: false,
    isStarted: function (){
        if (!this.started){
            this.started = true;
            return false;
        }
        return true;
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
        let cardNumber = elem.value.replace(/\s/g, '');
        let card = await this.getCardData(cardNumber);
        this.changeBrand(e, card);
        this.updateInstallmentsElement(e);
    },

    getCardData: async function (cardNumber) {
        let result = [];
        if (cardNumber.length < 6) {
            throw "Invalid card number";
        }
        let value = await this.getCardDataByApi(cardNumber);
        if (value === 'error' || typeof value == 'undefined') {
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
            $.ajax({
                type: "GET",
                dataType: "json",
                url: 'https://api.mundipagg.com/bin/v1/' + cardNumber,
                async: false,
                cache: true,
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
        let imgElem = $(elem).parent().find(brandImgTarget).find('img');
        $(elem).parent().find(brandTarget).attr('value', card[0].brand);
        if (imgElem.length) {
            imgElem.attr('src', imageSrc);
        } else {
            let img = $(document.createElement('img'));
            $(elem).parent().find(brandImgTarget).append(
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

    addsMask: function () {
        $('.pagarme-card-form-card-number').mask('0000 0000 0000 0000');
        $('.pagarme-card-form-card-expiry').mask('00 / 00');
        $('.pagarme-card-form-card-cvc').mask('0000');
        $('input[name*=\\[cpf\\]]').mask('000.000.000-00');
        $('input[name*=\\[zip_code\\]]').mask('00000-000');
        $('#billing_cpf').change(function () {
            $('input[name="pagarme[voucher][cards][1][document-holder]"]').empty();
            $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
        });
        $('input[name="pagarme[voucher][cards][1][document-holder]"]').val($('#billing_cpf').val()).mask("999.999.999-99").trigger('input');
    },

    updateInstallmentsElement: function (e) {
        let elem = e.currentTarget;
        let brand = $(elem).closest('fieldset').find(brandTarget).val();
        let total = $(elem).closest('fieldset').find(valueTarget).val();
        if (!total)
            total = cartTotal;
        if (!brand || !total)
            throw "Cant update installments: invalid total and/or brand";
        let storageName = btoa(brand + total);
        sessionStorage.removeItem(storageName);
        let storage = sessionStorage.getItem(storageName);
        let cardForm = $(elem).closest("fieldset");
        let select = cardForm.find(installmentsTarget);
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
        try {
            swal(message);
        } catch (e) {
            new swal(message);
        }
    },
    execute: async function () {
        let result = pagarmeCard.formHandler(),
            i = 1;
        try {
            while (!result && i <= this.limit) {
                if (i === this.limit) {
                    this.removeLoader(this.getCheckoutPaymentElement());
                    throw "Tokenize timeout";
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
    addEventListener: function () {
        jQuery(cardNumberTarget).on('blur', function (e) {
            pagarmeCard.keyEventHandlerCard(e);
        });
        $("form.checkout").on(
            "checkout_place_order",
            function (e) {
                e.preventDefault();
                if (pagarmeCard.isPagarmePayment() && !pagarmeCard.canSubmit) {
                    pagarmeCard.showLoader(pagarmeCard.getCheckoutPaymentElement());
                    pagarmeCard.execute();
                    return false;
                }
                return true;
            }
        );

    },
    start: function () {
        if (this.isStarted()) {
            return;
        }
        this.getCardsMethods();
        this.getBrands();
        this.addsMask();
        this.addEventListener();
    },
};
