/* globals pagarmeTds, pagarmeTdsToken, cartTotal, jQuery, wc_pagarme_checkout, PagarmeGlobalVars */
const pagarmeTdsNx = {
    TIMEOUT_MS: 30000,
    NX_FLOW: 'nx',
    LEGACY_FLOW: 'legacy',

    getTdsTokenNx: () => {
        try {
            const response = jQuery.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/wc-api/pagarme-tds-token-nx',
                async: false,
                cache: false,
            }).responseText;

            if (!response || response.length === 0) {
                console.warn('TDS-NX: Empty response from backend');
                return { token: null, flowPreference: pagarmeTdsNx.LEGACY_FLOW };
            }

            const parsed = JSON.parse(response);

            if (!parsed?.data) {
                console.warn('TDS-NX: Invalid response structure');
                return { token: null, flowPreference: pagarmeTdsNx.LEGACY_FLOW };
            }

            const token = parsed.data.token;
            const flowPreference = parsed.data.flow_preference || pagarmeTdsNx.LEGACY_FLOW;

            if (!token) {
                console.info('TDS-NX: Token is null, backend requested fallback to legacy');
            }

            return {
                token: token,
                flowPreference: flowPreference
            };
        } catch (e) {
            console.error('TDS-NX: Error fetching token:', e);
            return { token: null, flowPreference: pagarmeTdsNx.LEGACY_FLOW };
        }
    },

    loadScriptAsync: (url) => {
        return new Promise((resolve, reject) => {
            if (typeof window !== 'undefined' && window[url.includes('tifa') ? 'tifa' : 'ThreeDS'] !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = url;
            script.async = true;

            const timeout = setTimeout(() => {
                reject(new Error(`Script timeout loading ${url}`));
                script.remove();
            }, pagarmeTdsNx.TIMEOUT_MS);

            script.onload = () => {
                clearTimeout(timeout);
                resolve();
            };

            script.onerror = () => {
                clearTimeout(timeout);
                reject(new Error(`Failed to load script: ${url}`));
            };

            document.head.appendChild(script);
        });
    },

    formatTdsDataForNx: (cardExpiryDate, acctType = '02') => {
        const billingAddressStreet = jQuery('input[name="billing_address_1"]').val();
        const billingAddressNumber = jQuery('input[name="billing_number"]').val();
        const billingAddressComplement = jQuery('input[name="billing_address_2"]').val();
        const billingAddressCity = jQuery('input[name="billing_city"]').val();
        const billingAddressState = jQuery('select[name="billing_state"]').val();
        const billingAddressPostcode = jQuery('input[name="billing_postcode"]').val();

        let shippingAddressStreet = billingAddressStreet;
        let shippingAddressNumber = billingAddressNumber;
        let shippingAddressComplement = billingAddressComplement;
        let shippingAddressCity = billingAddressCity;
        let shippingAddressState = billingAddressState;
        let shippingAddressPostcode = billingAddressPostcode;

        if (jQuery('input[name="ship_to_different_address"]').is(':checked')) {
            shippingAddressStreet = jQuery('input[name="shipping_address_1"]').val();
            shippingAddressNumber = jQuery('input[name="shipping_number"]').val();
            shippingAddressComplement = jQuery('input[name="shipping_address_2"]').val();
            shippingAddressCity = jQuery('input[name="shipping_city"]').val();
            shippingAddressState = jQuery('select[name="shipping_state"]').val();
            shippingAddressPostcode = jQuery('input[name="shipping_postcode"]').val();
        }

        const customerPhones = [{
            country_code: '55',
            subscriber: pagarmeTdsNx.filterOnlyNumbers(jQuery('input[name="billing_phone"]').val()),
            phone_type: 'mobile',
        }];

        return {
            bill_addr: {
                street: billingAddressStreet,
                number: billingAddressNumber,
                complement: billingAddressComplement,
                city: billingAddressCity,
                state: billingAddressState,
                country: 'BRA',
                post_code: billingAddressPostcode,
            },
            ship_addr: {
                street: shippingAddressStreet,
                number: shippingAddressNumber,
                complement: shippingAddressComplement,
                city: shippingAddressCity,
                state: shippingAddressState,
                country: 'BRA',
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

    filterOnlyNumbers: (text) => {
        if (!text) return '';
        return text.replace(/[^0-9]/g, '');
    },

    executeNxFlow: async (nxToken, tdsData) => {
        if (!nxToken) {
            throw new Error('No 3DS-NX token available');
        }

        if (typeof window.tifa === 'undefined') {
            throw new Error('Tifa SDK not loaded');
        }

        return new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
                reject(new Error('3DS-NX authentication timeout'));
            }, pagarmeTdsNx.TIMEOUT_MS);

            try {
                window.tifa.init(
                    {
                        token: nxToken,
                        container: '#tdsMethodContainer',
                        challengeContainer: '#challengeContainer'
                    },
                    tdsData,
                    (result) => {
                        clearTimeout(timeout);
                        resolve(result);
                    },
                    (error) => {
                        clearTimeout(timeout);
                        reject(error);
                    }
                );
            } catch (e) {
                clearTimeout(timeout);
                reject(e);
            }
        });
    },

    formatNxResponse: (nxResult) => {
        if (!nxResult) {
            throw new Error('Empty NX result');
        }

        return {
            risk_id: nxResult.risk_id || nxResult.riskId,
            steps: [
                {
                    tds_server_trans_id: nxResult.tds_server_trans_id || nxResult.transactionId,
                    trans_status: nxResult.trans_status || nxResult.transStatus,
                    authenticated_card: nxResult.authenticated_card || nxResult.card,
                    challenge_canceled: nxResult.challenge_canceled || nxResult.challengeCanceled || false
                }
            ],
            _flow_type: 'nx'
        };
    },

    formatLegacyResponse: (legacyResult) => {
        return {
            ...legacyResult,
            _flow_type: 'legacy'
        };
    },

    handleFallback: (event) => {
        return new Promise((resolve) => {
            if (typeof pagarmeTds === 'undefined' || typeof pagarmeTds.callTdsLegacy !== 'function') {
                resolve(null);
                return;
            }

            const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();
            const expDate = jQuery(checkoutPaymentElement)
                .find(pagarmeCard.cardExpiryTarget)
                .val();
            let [expMonth, expYear] = expDate.split('/');
            expMonth = expMonth.trim();
            expYear = expYear.trim();
            expYear = `20${expYear}`;
            const cardExpiryDate = `${expYear}-${expMonth}`;

            const tdsData = pagarmeTds.getTdsData('02', cardExpiryDate);
            const legacyToken = pagarmeTds.getToken();

            if (!legacyToken) {
                resolve(null);
                return;
            }

            pagarmeTds.callTdsLegacy(
                legacyToken,
                tdsData,
                (legacyResult) => {
                    if (legacyResult?.error !== undefined) {
                        resolve(null);
                        return;
                    }
                    resolve(pagarmeTdsNx.formatLegacyResponse(legacyResult));
                }
            );
        });
    },

    execute: async (event) => {
        if (typeof pagarmeTds === 'undefined' || !pagarmeTds.canTdsRun()) {
            return false;
        }

        pagarmeCard.showLoader(event);
        pagarmeTds.checkoutEvent = event;
        pagarmeTds.addTdsAttributeData();

        const checkoutPaymentElement = pagarmeCard.getCheckoutPaymentElement();
        const expDate = jQuery(checkoutPaymentElement)
            .find(pagarmeCard.cardExpiryTarget)
            .val();
        let [expMonth, expYear] = expDate.split('/');
        expMonth = expMonth.trim();
        expYear = expYear.trim();
        expYear = `20${expYear}`;
        const cardExpiryDate = `${expYear}-${expMonth}`;

        try {
            const nxTokenData = pagarmeTdsNx.getTdsTokenNx();

            if (nxTokenData.token && nxTokenData.flowPreference === 'nx') {
                try {
                    await pagarmeTdsNx.loadScriptAsync(
                        document.currentScript?.getAttribute('data-tifa-url') ||
                        'https://tifa-app.stone.com.br/live/v1/tifa/tifa-app.min.js'
                    );
                    await pagarmeTdsNx.loadScriptAsync(
                        document.currentScript?.getAttribute('data-3ds-nx-url') ||
                        'https://3ds-nx-js.stone.com.br/live/v2/3ds2.min.js'
                    );

                    const tdsDataNx = pagarmeTdsNx.formatTdsDataForNx(cardExpiryDate);
                    const nxResult = await pagarmeTdsNx.executeNxFlow(nxTokenData.token, tdsDataNx);

                    const formattedResult = pagarmeTdsNx.formatNxResponse(nxResult);
                    pagarmeTds.createTdsField(JSON.stringify(formattedResult));
                    pagarmeCard.removeLoader(event);
                    pagarmeCard.executeAll(event);
                    return true;
                } catch (nxError) {
                    console.warn('3DS-NX failed, falling back to legacy handler:', nxError);
                }
            }

            const legacyResult = await pagarmeTdsNx.handleFallback(event);
            if (legacyResult) {
                pagarmeTds.createTdsField(JSON.stringify(legacyResult));
                pagarmeCard.removeLoader(event);
                pagarmeCard.executeAll(event);
                return true;
            }

            pagarmeCard.removeLoader(event);
            pagarmeTds.removeTdsAttributeData();
            return false;
        } catch (error) {
            console.error('TDS execution error:', error);
            pagarmeCard.removeLoader(event);
            pagarmeTds.removeTdsAttributeData();
            pagarmeCard.showErrorInPaymentMethod(
                PagarmeGlobalVars.checkoutErrors.pt_BR['fail_get_token'] ||
                'Erro ao processar autenticação 3DS'
            );
            return false;
        }
    }
};
