/* globals cartTotal */
const pagarmeTds = {
    authentication: "authentication",
    vendor: "pagarme",
    checkoutEvent: null,
    paymentMethodTarget: "data-pagarmecheckout-method",
    sequenceTarget: "data-pagarmecheckout-card-num",
    elementTarget: "data-pagarmecheckout-element",
    formTarget: "data-pagarmecheckout-form",
    FAIL_GET_EMAIL: "fail_get_email",
    FAIL_GET_BILLING_ADDRESS: "fail_get_billing_address",
    FAIL_ASSEMBLE_CARD_EXPIRY_DATE: "fail_assemble_card_expiry_date",
    FAIL_ASSEMBLE_PURCHASE: "fail_assemble_purchase",
    
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
            return;
        }

        pagarmeCard.showErrorInPaymentMethod(
            pagarmeTds.getErrorMessage(errors.error)
        );
    },

    getErrorMessage: (error) => {
        const messages = PagarmeGlobalVars.checkoutErrors.pt_BR;
        if (typeof error === "string" && messages[error]) {
            return messages[error];
        }
        return messages.serviceUnavailable;
    },

    getToken: () => {
        const data = pagarmeTdsToken.getToken();
        if (data.error) {
            pagarmeTds.removeTdsAttributeData();
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[data.error]
            );
            return null;
        }

        return data;
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

    getCustomerPhone: () => {
        const rawPhone = pagarmeTds.filterOnlyNumber(
            jQuery('input[name="billing_phone"]').val()
        );

        return {
            country_code: "55",
            area_code: rawPhone.slice(0, 2),
            number: rawPhone.slice(2),
        };
    },

    getAddress: (prefix) => {
        const street = jQuery(`input[name="${prefix}_address_1"]`).val();
        const number = jQuery(`input[name="${prefix}_number"]`).val();
        const complement = jQuery(`input[name="${prefix}_address_2"]`).val();

        return {
            country: "BR",
            state: jQuery(`select[name="${prefix}_state"]`).val(),
            city: jQuery(`input[name="${prefix}_city"]`).val(),
            zip_code: jQuery(`input[name="${prefix}_postcode"]`).val(),
            line_1: `${street}, ${number}`,
            line_2: complement || "",
        };
    },

    getTdsData: (cardData) => {
        const billingAddress = pagarmeTds.getAddress("billing");
        const shippingPrefix = jQuery(
            'input[name="ship_to_different_address"]'
        ).is(":checked")
            ? "shipping"
            : "billing";
        const shippingAddress = pagarmeTds.getAddress(shippingPrefix);

        const firstName = jQuery('input[name="billing_first_name"]').val();
        const lastName = jQuery('input[name="billing_last_name"]').val();
        const shippingFirstName =
            jQuery(`input[name="${shippingPrefix}_first_name"]`).val() ||
            firstName;
        const shippingLastName =
            jQuery(`input[name="${shippingPrefix}_last_name"]`).val() ||
            lastName;

        const customerDocument = pagarmeTds.filterOnlyNumber(
            jQuery('#billing_document').val() ||
                jQuery('#billing_cpf').val() ||
                ""
        );

        return {
            customer: {
                name: `${firstName} ${lastName}`.trim(),
                email: jQuery('input[name="billing_email"]').val(),
                document: customerDocument,
                phones: {
                    mobile_phone: pagarmeTds.getCustomerPhone(),
                },
            },
            shipping: {
                recipient_name: `${shippingFirstName} ${shippingLastName}`.trim(),
                address: shippingAddress,
            },
            payments: [
                {
                    payment_method: "credit_card",
                    credit_card: {
                        card: {
                            number: cardData.number,
                            holder_name: cardData.holderName,
                            exp_month: cardData.expMonth,
                            exp_year: cardData.expYear,
                            billing_address: billingAddress,
                        },
                    },
                    amount: parseInt(cartTotal * 100),
                },
            ],
        };
    },

    callTds: (tokenData) => {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();

        const expDate = jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardExpiryTarget)
            .val();
        let [expMonth, expYear] = expDate.split("/");
        expMonth = expMonth.trim();
        expYear = `20${expYear.trim()}`;

        const cardNumber = pagarmeTds.filterOnlyNumber(
            jQuery(checkoutPaymentElement)
                .find(pagarmeCard.cardNumberTarget)
                .val()
        );
        const holderName = jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardHolderNameTarget)
            .val();

        const tdsData = pagarmeTds.getTdsData({
            number: cardNumber,
            holderName,
            expMonth,
            expYear,
        });
        initTds.callTdsFunction(
            tokenData,
            tdsData,
            pagarmeTds.callbackTds.bind(this)
        );
    },

    callbackTds: (data) => {
        pagarmeCard.removeLoader(pagarmeTds.checkoutEvent);
        if (data?.error !== undefined) {
            pagarmeTds.addErrors(data);
            return;
        }
        if (data?.trans_status === "" || data?.trans_status === undefined) {
            return;
        }

        if (pagarmeTds.checkoutEvent === null) {
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTdsToken.FAIL_GET_TOKEN
                ]
            );
            return;
        }

        const authentication = JSON.stringify(data);
        pagarmeTds.createTdsField(authentication);

        pagarmeCard.executeAll(pagarmeTds.checkoutEvent);
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
        return text.replace(/[^0-9]/g, "");
    },

    start: (event) => {
        const canTdsRun = pagarmeTds.canTdsRun();
        if (canTdsRun) {
            pagarmeCard.showLoader(event);
            pagarmeTds.checkoutEvent = event;
            pagarmeTds.addTdsAttributeData();
            const tokenData = pagarmeTds.getToken();
            if (!tokenData?.token) {
                return false;
            }

            pagarmeTds.callTds(tokenData);
        }

        return canTdsRun;
    },
};
