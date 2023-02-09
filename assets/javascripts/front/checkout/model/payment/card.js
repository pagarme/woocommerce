/* globals wc_pagarme_checkout */

(function ($) {
        const cardEl = $('[data-element="fields-cc-data"]');
        const cardNumberTarget = 'input[data-element="pagarme-card-number"]';
        const brandTarget = '[data-pagarmecheckout-element="brand-input"]';
        const brandImgTarget = 'span[name="brand-image"]';
        const installmentsTarget = '[data-pagarme-component="installments"]';
        const mundiCdn = 'https://cdn.mundipagg.com/assets/images/logos/brands/png/';
        const limit = 10;
        const tokenElement = '#pagarmetoken';
        let canSubmit = false;
        let cardsMethods = [];
        let brands = [];

        let pagarme = {
            getCheckoutPaymentElement: function () {
                let value = $('form.checkout input[name="payment_method"]:checked').val();
                return $('.wc_payment_method.payment_method_' + value);
            },
            isPagarmePayment: function () {
                return $('form.checkout input[name="payment_method"]:checked').val().indexOf('pagarme');
            },
            keyEventHandlerCard: function (e) {
                loadBrand(e);
            },
            formHandler: function () {
                if (pagarme.isPagarmePayment()) {
                    let e = pagarme.getCheckoutPaymentElement();
                    let cardsForm = e.find('fieldset[data-pagarmecheckout="card"]');
                    return checkTokenCard(cardsForm);
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
        };

        function checkTokenCard(e)
        {
            let allResult = [];
            e.each(async function () {
                if (pagarme.hasSelectedWallet(this)) {
                    allResult.push(true);
                    return;
                }
                allResult.push(checkToken(this));
            });
            return !allResult.includes(false);
        }

        async function wait(ms = 1000) {
            return new Promise(resolve => {
                setTimeout(resolve, ms);
            });
        }

        function checkToken(e, step = 0) {
            if (!(e instanceof jQuery)) {
                e = $(e);
            }
            if (e.find(tokenElement).length) {
                return true;
            }
            return false;
        }

        async function getCardDataContingency(cardNumber) {
            let oldPrefix = '',
                types = await getBrands(true),
                bin = cardNumber.substr(0, 6),
                currentBrand,
                data;
            for (var i = 0; i < types.length; i += 1) {
                var current_type = types[i];
                for (var j = 0; j < current_type.prefixes.length; j += 1) {
                    var prefix = current_type.prefixes[j].toString();
                    if (bin.indexOf(prefix) === 0 && oldPrefix.length < prefix.length) {
                        oldPrefix = prefix;
                        currentBrand = current_type.brand;
                        data = current_type;
                    }
                }
            }
            return data;
        }

        function getBrands(onlyBrands = false) {
            return new Promise((resolve) => {
                if (onlyBrands) {
                    let types = [];
                    cardsMethods.forEach(function (key) {
                        $.each(wc_pagarme_checkout.config.payment[key].brands, function (method) {
                            types.push(this);
                        });
                    });
                    resolve(types);
                }
                cardsMethods.forEach(function (key) {
                    brands[key] = wc_pagarme_checkout.config.payment[key].brands;
                });
            });
        }

        function getCardsMethods() {
            $.each(wc_pagarme_checkout.config.payment, function (method) {
                if (wc_pagarme_checkout.config.payment[method].is_card) {
                    cardsMethods.push(method);
                }
            });
        }

        async function loadBrand(e) {
            let elem = e.currentTarget;
            let cardNumber = elem.value.replace(/\s/g, '');
            let card = await getCardData(cardNumber);
            changeBrand(e, card);
            updateInstallmentsElement(e);
        }

        async function getCardData(cardNumber) {
            let result = [];
            if (cardNumber.length < 6) {
                throw "Invalid card number";
            }
            let value = await getCardDataByApi(cardNumber);
            if (value === 'error' || typeof value == 'undefined') {
                value = await getCardDataContingency(cardNumber);
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
        }

        function getCardDataByApi(cardNumber) {
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
        }

        function changeBrand(e, card) {
            if (typeof e == 'undefined' || typeof card == 'undefined') {
                throw "Invalid data to change card brand";
            }
            let elem = e.currentTarget;
            let imageSrc = getImageSrc(card);
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
        }

        function getImageSrc(card) {
            if (card[0].image) {
                return card[0].image;
            }
            return mundiCdn + card[0].brand + '.png';
        }

        function addsMask() {
            $('.pagarme-card-form-card-number').mask('0000 0000 0000 0000');
            $('.pagarme-card-form-card-expiry').mask('00 / 00');
            $('.pagarme-card-form-card-cvc').mask('0000');
            $('#billet-value').mask('#.##0,00', {
                reverse: true
            });
            $('input[name*=\\[cpf\\]]').mask('000.000.000-00');
            $('input[name*=\\[zip_code\\]]').mask('00000-000');
        }

        function updateInstallmentsElement(e) {
            let elem = e.currentTarget;
            let brand = $(elem).parent().find(brandTarget).val(),
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
                ajax.done($.proxy(_done, this, select, storageName, cardForm));
                ajax.fail(function () {
                    removeLoader(cardForm);
                });
                showLoader(cardForm);
            }
        }

        const _done = function (select, storageName, e, response) {
            select.html(response);
            sessionStorage.setItem(storageName, response);
            removeLoader(e);
        };

        const _fail = function (e) {
            removeLoader(e);
        };

        const removeLoader = function (e) {
            if (!(e instanceof jQuery)) {
                e = $(e);
            }
            e.unblock();
        };

        const showLoader = function (e) {
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
        };

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

        async function execute() {
            let result = pagarme.formHandler(),
                i = 1;
            try {
                while(!result && i <= limit) {
                    if (i === limit) {
                        throw "Tokenize timeout";
                    }
                    await wait();
                    result = pagarme.formHandler();
                    i++;
                }
                canSubmit = true;
                $("form.checkout, form#order_review").submit();
            } catch (er) {
                if (typeof er === 'string') {
                    showError(er);
                } else {
                    showError(er.message);
                }
            }
        }

        $(cardNumberTarget).on('blur', function (e) {
            pagarme.keyEventHandlerCard(e);
        });

        $( document ).ready(function() {
            getCardsMethods();
            getBrands();
            addsMask();
        });

        $("form.checkout").on(
            "checkout_place_order",
            function (e) {
                e.preventDefault();
                if (pagarme.isPagarmePayment() && !canSubmit) {
                    execute();
                    return false;
                }
                return true;
            }
        );
    } (jQuery)
);
