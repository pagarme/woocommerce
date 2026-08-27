/* globals cartTotal, jQuery, pagarmeCard, pagarmeTdsNx, pagarmeTdsToken, initTds, wc_pagarme_checkout, PagarmeGlobalVars */
/* jshint esversion: 11 */

/**
 * Único ponto de decisão entre 3DS-NX (AuthSwitch) e 3DS legado.
 *
 * Contrato:
 * - `start()` é chamado pelo `checkout_place_order` e SEMPRE retorna de forma síncrona.
 *   Retornar `true` trava o submit do WooCommerce; quem libera é `finish()`/`abort()`.
 * - `runFlow()` é a única corrotina do fluxo. O 3DS legado só é alcançável quando o
 *   NX se declara indisponível (`status === 'unavailable'`).
 * - `flowInProgress` impede reentrância: um segundo clique não inicia um novo fluxo.
 */
const pagarmeTds = {
    authentication: "authentication",
    vendor: "pagarme",
    checkoutEvent: null,
    flowInProgress: false,
    TIMEOUT_MS: 30000,
    paymentMethodTarget: "data-pagarmecheckout-method",
    sequenceTarget: "data-pagarmecheckout-card-num",
    elementTarget: "data-pagarmecheckout-element",
    formTarget: "data-pagarmecheckout-form",
    FAIL_GET_EMAIL: "fail_get_email",
    FAIL_GET_BILLING_ADDRESS: "fail_get_billing_address",
    FAIL_ASSEMBLE_CARD_EXPIRY_DATE: "fail_assemble_card_expiry_date",
    FAIL_ASSEMBLE_PURCHASE: "fail_assemble_purchase",
    STATUS_AUTHENTICATED: "authenticated",
    STATUS_DENIED: "denied",
    STATUS_UNAVAILABLE: "unavailable",

    addErrors: (errors) => {
        if (errors.error?.email) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_GET_EMAIL
                ]
            );
            return;
        }
        if (errors.error?.bill_addr) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_GET_BILLING_ADDRESS
                ]
            );
            return;
        }
        if (errors.error?.card_expiry_date) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_ASSEMBLE_CARD_EXPIRY_DATE
                ]
            );
            return;
        }
        if (errors.error?.purchase) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_ASSEMBLE_PURCHASE
                ]
            );
        }
    },

    canTdsRun: () => {
        const fieldset = pagarmeCard
            .getCheckoutPaymentElement()
            .find(pagarmeCard.fieldsetCardElements);

        const paymentMethod = fieldset.attr(pagarmeTds.paymentMethodTarget);
        return (
            paymentMethod === "credit_card" &&
            wc_pagarme_checkout.config.payment.credit_card.tdsEnabled ===
                true &&
            cartTotal >=
                wc_pagarme_checkout.config.payment.credit_card.tdsMinAmount &&
            pagarmeCard.brandIsVisaOrMaster() &&
            !pagarmeTds.hasAuthenticationField()
        );
    },

    addTdsAttributeData: () => {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();
        jQuery("form.checkout").attr(pagarmeTds.formTarget, "");
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardHolderNameTarget)
            .attr(pagarmeTds.elementTarget, "holder_name");
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardNumberTarget)
            .attr(pagarmeTds.elementTarget, "number");
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.brandTarget)
            .attr(pagarmeTds.elementTarget, "brand");
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardCvvTarget)
            .attr(pagarmeTds.elementTarget, "cvv");
    },

    removeTdsAttributeData: () => {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();
        jQuery("form.checkout").removeAttr(pagarmeTds.formTarget);
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardHolderNameTarget)
            .removeAttr(pagarmeTds.elementTarget);
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardNumberTarget)
            .removeAttr(pagarmeTds.elementTarget);
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.brandTarget)
            .removeAttr(pagarmeTds.elementTarget);
        jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardCvvTarget)
            .removeAttr(pagarmeTds.elementTarget);
    },

    /**
     * @returns {string} `YYYY-MM` ou string vazia quando o campo está incompleto.
     */
    getCardExpiryDate: () => {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();
        const expDate = jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardExpiryTarget)
            .val();

        if (!expDate) {
            return "";
        }

        const [rawMonth, rawYear] = expDate.split("/");
        if (!rawMonth || !rawYear) {
            return "";
        }

        return `20${rawYear.trim()}-${rawMonth.trim()}`;
    },

    getTdsData: (acctType, cardExpiryDate) => {
        const customerPhones = [
            {
                country_code: "55",
                subscriber: pagarmeTds.filterOnlyNumber(
                    jQuery('input[name="billing_phone"]').val()
                ),
                phone_type: "mobile",
            },
        ];

        const billingAddressStreet = jQuery(
            'input[name="billing_address_1"]'
        ).val();
        const billingAddressNumber = jQuery(
            'input[name="billing_number"]'
        ).val();
        const billingAddressComplement = jQuery(
            'input[name="billing_address_2"]'
        ).val();
        const billingAddressCity = jQuery('input[name="billing_city"]').val();
        const billingAddressState = jQuery(
            'select[name="billing_state"]'
        ).val();
        const billingAddressPostcode = jQuery(
            'input[name="billing_postcode"]'
        ).val();

        let shippingAddressStreet = billingAddressStreet;
        let shippingAddressNumber = billingAddressNumber;
        let shippingAddressComplement = billingAddressComplement;
        let shippingAddressCity = billingAddressCity;
        let shippingAddressState = billingAddressState;
        let shippingAddressPostcode = billingAddressPostcode;

        if (jQuery('input[name="ship_to_different_address"]').is(":checked")) {
            shippingAddressStreet = jQuery(
                'input[name="shipping_address_1"]'
            ).val();
            shippingAddressNumber = jQuery(
                'input[name="shipping_number"]'
            ).val();
            shippingAddressComplement = jQuery(
                'input[name="shipping_address_2"]'
            ).val();
            shippingAddressCity = jQuery('input[name="shipping_city"]').val();
            shippingAddressState = jQuery(
                'select[name="shipping_state"]'
            ).val();
            shippingAddressPostcode = jQuery(
                'input[name="shipping_postcode"]'
            ).val();
        }

        return {
            bill_addr: {
                street: billingAddressStreet,
                number: billingAddressNumber,
                complement: billingAddressComplement,
                city: billingAddressCity,
                state: billingAddressState,
                country: "BRA",
                post_code: billingAddressPostcode,
            },
            ship_addr: {
                street: shippingAddressStreet,
                number: shippingAddressNumber,
                complement: shippingAddressComplement,
                city: shippingAddressCity,
                state: shippingAddressState,
                country: "BRA",
                post_code: shippingAddressPostcode,
            },
            email: jQuery('input[name="billing_email"]').val(),
            phones: customerPhones,
            card_expiry_date: cardExpiryDate,
            purchase: {
                amount: parseInt(cartTotal * 100),
                date: new Date().toISOString(),
                instal_data: 2,
            },
            acct_type: acctType,
        };
    },

    createTdsField: (authentication) => {
        pagarmeTds.removeTdsFields();
        const fieldset = pagarmeCard
            .getCheckoutPaymentElement()
            .find(pagarmeCard.fieldsetCardElements);
        const inputName = `${pagarmeTds.vendor}[${fieldset.attr(
            pagarmeTds.paymentMethodTarget
        )}][cards][${fieldset.attr(pagarmeTds.sequenceTarget)}][${
            pagarmeTds.authentication
        }]`;
        const input = jQuery(document.createElement("input"));
        input
            .attr("type", "hidden")
            .attr("name", inputName)
            .attr("id", inputName)
            .attr("value", authentication)
            .attr(pagarmeTds.elementTarget, pagarmeTds.authentication);
        fieldset.append(input);
    },

    removeTdsFields: () => {
        const field = pagarmeCard.getCheckoutPaymentElement();
        const inputs = field.find(
            `[${pagarmeTds.elementTarget}=${pagarmeTds.authentication}]`
        );
        if (inputs.length) {
            jQuery.each(inputs, function () {
                this.remove();
            });
        }
    },

    hasAuthenticationField: () => {
        return (
            pagarmeCard
                .getCheckoutPaymentElement()
                .find(
                    `[${pagarmeTds.elementTarget}=${pagarmeTds.authentication}]`
                ).length > 0
        );
    },

    filterOnlyNumber: (text) => {
        if (!text) {
            return "";
        }
        return text.replace(/[^0-9]/g, "");
    },

    isNxAvailable: () => {
        return (
            typeof pagarmeTdsNx === "object" &&
            typeof pagarmeTdsNx.attempt === "function"
        );
    },

    /**
     * Chamado pelo `checkout_place_order`. Nunca dispara AJAX diretamente.
     *
     * @returns {boolean} `true` trava o submit do WooCommerce.
     */
    start: (event) => {
        // Já existe um fluxo 3DS rodando: trava o checkout e NÃO dispara nada de novo.
        if (pagarmeTds.flowInProgress) {
            return true;
        }

        if (!pagarmeTds.canTdsRun()) {
            return false;
        }

        pagarmeTds.flowInProgress = true;
        pagarmeTds.runFlow(event).finally(() => {
            pagarmeTds.flowInProgress = false;
        });

        return true;
    },

    /**
     * Fluxo sequencial estrito: NX primeiro, legado apenas no bloco de indisponibilidade.
     */
    runFlow: async (event) => {
        pagarmeCard.showLoader(event);
        pagarmeTds.checkoutEvent = event;
        pagarmeTds.addTdsAttributeData();

        try {
            // ---------- TENTATIVA 1: 3DS-NX (bloqueante) ----------
            if (pagarmeTds.isNxAvailable()) {
                const nx = await pagarmeTdsNx.attempt();

                if (nx.status === pagarmeTds.STATUS_AUTHENTICATED) {
                    // FIM DO FLUXO. O legado nunca é chamado.
                    return pagarmeTds.finish(event, nx.authentication);
                }

                if (nx.status === pagarmeTds.STATUS_DENIED) {
                    // Negado/cancelado pelo emissor não é falha técnica: sem fallback.
                    return pagarmeTds.abort(nx.errorKey);
                }
                // status === 'unavailable' → única porta de entrada para o legado.
            }

            // ---------- TENTATIVA 2: 3DS legado (só aqui) ----------
            const legacy = await pagarmeTds.runLegacy();
            if (!legacy.authentication) {
                return pagarmeTds.abort(legacy.errorKey);
            }

            return pagarmeTds.finish(event, legacy.authentication);
        } catch (error) {
            return pagarmeTds.abort(pagarmeTdsToken.FAIL_GET_TOKEN);
        }
    },

    /**
     * Efeitos do sucesso. Fica fora de qualquer `try` de fallback de propósito:
     * uma falha aqui não pode reabrir o fluxo legado.
     */
    finish: (event, authentication) => {
        pagarmeTds.createTdsField(JSON.stringify(authentication));
        pagarmeCard.removeLoader(event);
        pagarmeCard.executeAll(event);
        return true;
    },

    /**
     * @param {string|null} errorKey Chave em `PagarmeGlobalVars.checkoutErrors.pt_BR`.
     *                               `null` quando a mensagem já foi exibida por `addErrors`.
     */
    abort: (errorKey) => {
        pagarmeCard.removeLoader(pagarmeTds.checkoutEvent);
        pagarmeTds.removeTdsAttributeData();

        if (errorKey) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[errorKey] ||
                    PagarmeGlobalVars.checkoutErrors.pt_BR[
                        pagarmeTdsToken.FAIL_GET_TOKEN
                    ]
            );
        }

        return false;
    },

    /**
     * 3DS legado promisificado. Nunca é chamado em paralelo com o NX.
     *
     * @returns {Promise<{authentication?: object, errorKey?: string|null}>}
     */
    runLegacy: async () => {
        const cardExpiryDate = pagarmeTds.getCardExpiryDate();
        if (!cardExpiryDate) {
            return { errorKey: pagarmeTds.FAIL_ASSEMBLE_CARD_EXPIRY_DATE };
        }

        const tokenData = await pagarmeTdsToken.getToken();
        if (tokenData.error || !tokenData.token) {
            return { errorKey: pagarmeTdsToken.FAIL_GET_TOKEN };
        }

        const tdsData = pagarmeTds.getTdsData("02", cardExpiryDate);

        return new Promise((resolve) => {
            let settled = false;
            let timeout = null;
            const settle = (result) => {
                if (settled) {
                    return;
                }
                settled = true;
                clearTimeout(timeout);
                resolve(result);
            };

            timeout = setTimeout(() => {
                settle({ errorKey: pagarmeTdsToken.FAIL_GET_TOKEN });
            }, pagarmeTds.TIMEOUT_MS);

            try {
                initTds.callTdsFunction(tokenData.token, tdsData, (data) => {
                    if (data?.error !== undefined) {
                        pagarmeTds.addErrors(data);
                        settle({ errorKey: null });
                        return;
                    }
                    if (!data?.trans_status) {
                        settle({ errorKey: pagarmeTdsToken.FAIL_GET_TOKEN });
                        return;
                    }
                    settle({
                        authentication: { ...data, _flow_type: "legacy" },
                    });
                });
            } catch (error) {
                settle({ errorKey: pagarmeTdsToken.FAIL_GET_TOKEN });
            }
        });
    },
};
