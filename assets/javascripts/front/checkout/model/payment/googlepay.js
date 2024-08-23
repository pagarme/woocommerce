/* globals wc_pagarme_checkout */
/* jshint esversion: 11 */
let pagarmeGooglePay = {
    getGooglePaymentsClient: function () {
        let self = this;
        let environment = "TEST";
        // if (window.checkoutConfig.pagarme_is_sandbox_mode === false) {
        //     environment = "PRODUCTION";
        // }

        return new google.payments.api.PaymentsClient({
            environment: environment,
        });
    },
    addGooglePayButton: function () {
        let self = this;
        let paymentsClient = this.getGooglePaymentsClient();
        const button = paymentsClient.createButton({
            buttonColor: "default",
            buttonType: "pay",
            buttonRadius: 5,
            buttonLocale: "pt",
            buttonSizeMode: "fill",
            onClick: this.onGooglePaymentButtonClicked
            
        });
        document.getElementById("pagarme-googlepay").appendChild(button);
    },

    onPaymentAuthorized: function (paymentData) {
        return new Promise(function (resolve, reject) {
            processPayment(paymentData)
                .then(function () {
                    resolve({ transactionState: "SUCCESS" });
                })
                .catch(function () {
                    resolve({
                        transactionState: "ERROR",
                        error: {
                            intent: "PAYMENT_AUTHORIZATION",
                            message: "Insufficient funds",
                            reason: "PAYMENT_DATA_INVALID",
                        },
                    });
                });
        });
    },

    getGooglePaymentDataRequest: function () {
        const baseRequest = {
            apiVersion: 2,
            apiVersionMinor: 0,
        };
        const tokenizationSpecification = {
            type: "PAYMENT_GATEWAY",
            parameters: {
                gateway: "pagarme",
                gatewayMerchantId: wc_pagarme_googlepay.accountId,
            },
        };

        const baseCardPaymentMethod = {
            type: "CARD",
            parameters: {
                allowedAuthMethods: ["PAN_ONLY"],
                allowedCardNetworks:  ["ELO", "MASTERCARD", "VISA"],
            },
        };
        const cardPaymentMethod = Object.assign({}, baseCardPaymentMethod, {
            tokenizationSpecification: tokenizationSpecification,
        });
        const paymentDataRequest = Object.assign({}, baseRequest);
        paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
        paymentDataRequest.transactionInfo =
            this.getGoogleTransactionInfo();
        paymentDataRequest.merchantInfo = {
            merchantId: wc_pagarme_googlepay.merchantId,
            merchantName: wc_pagarme_googlepay.merchantName,
        };
        return paymentDataRequest;
    },

    onGooglePaymentButtonClicked: function () {
        const self = this;
        const paymentDataRequest = pagarmeGooglePay.getGooglePaymentDataRequest();
        paymentDataRequest.transactionInfo = pagarmeGooglePay.getGoogleTransactionInfo();

        const paymentsClient = pagarmeGooglePay.getGooglePaymentsClient();
        paymentsClient
            .loadPaymentData(paymentDataRequest)
            .then(function (paymentData) {
                console.log(paymentData);
                pagarmeGooglePay.processPayment(paymentData, self);
            })
            .catch(function (err) {
                if (err.statusCode === "CANCELED") {
                    return;
                }
                console.error(err);
            });
    },

    getGoogleTransactionInfo: function () {
        // debugger;
        return {
            countryCode: "BR",
            currencyCode: "BRL",
            totalPriceStatus: "FINAL",
            totalPrice: cartTotal.toString(),
        };
    },

    prefetchGooglePaymentData: function () {
        const paymentDataRequest = this.getGooglePaymentDataRequest();
        paymentDataRequest.transactionInfo = {
            totalPriceStatus: "NOT_CURRENTLY_KNOWN",
            currencyCode: "BRL",
        };
        const paymentsClient = this.getGooglePaymentsClient();
        paymentsClient.prefetchPaymentData(paymentDataRequest);
    },


    processPayment: function(paymentData) {
        const checkoutPaymentElement = pagarmeGooglePay.getCheckoutPaymentElement();
        let inputName =  'pagarme[googlepay][googlepay][payload]'
        let input = jQuery(document.createElement('input'));
        if (!(checkoutPaymentElement instanceof jQuery)) {
            checkoutPaymentElement = jQuery(checkoutPaymentElement);
        }
        input.attr('type', 'hidden')
            .attr('name', inputName)
            .attr('id', "googlepaytoken")
            .attr('value', paymentData.paymentMethodData.tokenizationData.token)
        checkoutPaymentElement.append(input);
        checkoutPaymentElement.submit();
        jQuery('form#order_review').submit();
    },
    getCheckoutPaymentElement: function () {
        const value = jQuery('form .payment_methods input[name="payment_method"]:checked').val();
        return jQuery('.wc_payment_method.payment_method_' + value);
    },

    addEventListener: function () {
        jQuery(document.body).on('updated_checkout', function () {
            pagarmeGooglePay.addGooglePayButton();
        })

        jQuery(`${this.fieldsetCardElements} input`).on('change', function () {
            pagarmeGooglePay.clearErrorMessages();
        });
    },
    start: function () {
        this.addEventListener();
    }
};
pagarmeGooglePay.start();
