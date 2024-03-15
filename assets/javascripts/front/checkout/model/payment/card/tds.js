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
            pagarmeTds.showError(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_GET_EMAIL
                ]
            );
            return;
        }
        if (errors.error?.bill_addr) {
            pagarmeTds.showError(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_GET_BILLING_ADDRESS
                ]
            );
            return;
        }
        if (errors.error?.card_expiry_date) {
            pagarmeTds.showError(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_ASSEMBLE_CARD_EXPIRY_DATE
                ]
            );
            return;
        }
        if (errors.error?.purchase) {
            pagarmeTds.showError(
                PagarmeGlobalVars.checkoutErrors.pt_BR[
                    pagarmeTds.FAIL_ASSEMBLE_PURCHASE
                ]
            );
            return;
        }
    },

    getToken: () => {
        const data = pagarmeTdsToken.getToken();
        if (data.error) {
            pagarmeTds.removeTdsAttributeData();
            pagarmeTds.showError(
                PagarmeGlobalVars.checkoutErrors.pt_BR[data.error]
            );
            return "";
        }

        return data.token;
    },

    canTdsRun: () => {
        return (
            wc_pagarme_checkout.config.payment.credit_card.tdsEnabled ===
                true &&
            cartTotal * 100 >
                wc_pagarme_checkout.config.payment.credit_card.tdsMinAmount *
                    100 &&
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
                amount: cartTotal * 100,
                date: new Date().toISOString(),
                instal_data: 2,
            },
            acct_type: acctType,
        };
    },

    callTds: (tdsToken) => {
        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();

        const expDate = jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardExpiryTarget)
            .val();
        let [expMonth, expYear] = expDate.split("/");
        expMonth = expMonth.trim();
        expYear = expYear.trim();
        expYear = `20${expYear}`;

        const cardExpiryDate = `${expYear}-${expMonth}`;

        const tdsData = pagarmeTds.getTdsData("02", cardExpiryDate);
        initTds.callTdsFunction(
            tdsToken,
            tdsData,
            pagarmeTds.callbackTds.bind(this)
        );
    },

    showError: function (error) {
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

    callbackTds: (data) => {
        pagarmeCard.removeLoader();
        if (data?.error !== undefined) {
            pagarmeTds.addErrors(data);
            return;
        }
        if (data?.trans_status === "" || data?.trans_status === undefined) {
            return;
        }

        if (pagarmeTds.checkoutEvent === null) {
            pagarmeTds.showError(
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
        const field = pagarmeCard
            .getCheckoutPaymentElement()
            .find(pagarmeCard.fieldsetCardElements);
        let inputName =
            pagarmeTds.vendor +
            "[" +
            field.attr(pagarmeTds.paymentMethodTarget) +
            "][cards][" +
            field.attr(pagarmeTds.sequenceTarget) +
            "][" +
            pagarmeTds.authentication +
            "]";
        const input = jQuery(document.createElement("input"));
        input
            .attr("type", "hidden")
            .attr("name", inputName)
            .attr("id", inputName)
            .attr("value", authentication)
            .attr(pagarmeTds.elementTarget, pagarmeTds.authentication);
        field.append(input);
    },

    removeTdsFields: () => {
        const field = pagarmeCard.getCheckoutPaymentElement();
        let inputs = field.find(
            "[" +
                pagarmeTds.elementTarget +
                "=" +
                pagarmeTds.authentication +
                "]"
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
                    "[" +
                        pagarmeTds.elementTarget +
                        "=" +
                        pagarmeTds.authentication +
                        "]"
                ).length > 0
        );
    },

    filterOnlyNumber: (text) => {
        return text.replace(/[^0-9]/g, "");
    },

    start: (event) => {
        const canTdsRun = pagarmeTds.canTdsRun();
        if (canTdsRun) {
            pagarmeCard.showLoader();
            pagarmeTds.checkoutEvent = event;
            pagarmeTds.addTdsAttributeData();
            const token = pagarmeTds.getToken();
            if (token.length === 0) {
                return;
            }

            pagarmeTds.callTds(token);
        }

        return canTdsRun;
    },
};
