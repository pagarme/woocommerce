/* globals jQuery, pagarmeTds, pagarmeTdsToken */
/* jshint esversion: 11 */

/**
 * 3DS-NX (AuthSwitch).
 *
 * Este módulo NÃO conhece o 3DS legado: não chama `pagarmeTdsToken`, não chama
 * `initTds` e não chama `pagarmeCard.executeAll`. Ele apenas devolve um veredito
 * para `pagarmeTds.runFlow`, que é o único dono da decisão de fallback.
 *
 * `attempt()` nunca lança:
 * - `{ status: 'authenticated', authentication }` → NX autenticou.
 * - `{ status: 'denied', errorKey }`              → NX respondeu negando/cancelando (terminal).
 * - `{ status: 'unavailable' }`                   → falha técnica antes do NX responder.
 */
const pagarmeTdsNx = {
    TIMEOUT_MS: 30000,
    NX_FLOW: 'nx',
    LEGACY_FLOW: 'legacy',
    containerTarget: '#tdsMethodContainer',
    challengeTarget: '#challengeContainer',
    tifaUrlAttribute: 'data-tifa-url',
    nxUrlAttribute: 'data-3ds-nx-url',

    /**
     * URLs dos SDKs, resolvidas pelo PHP (respeitam sandbox/produção).
     *
     * @returns {{tifa: string|undefined, nx: string|undefined}}
     */
    getSdkUrls: () => {
        const container = jQuery(pagarmeTdsNx.containerTarget);
        return {
            tifa: container.attr(pagarmeTdsNx.tifaUrlAttribute),
            nx: container.attr(pagarmeTdsNx.nxUrlAttribute),
        };
    },

    /**
     * @returns {Promise<{token: string|null, flowPreference: string}>}
     */
    getTdsTokenNx: () => {
        return new Promise((resolve) => {
            let request;

            const timeout = setTimeout(() => {
                if (request) {
                    request.abort();
                }
                resolve({ token: null, flowPreference: pagarmeTdsNx.LEGACY_FLOW });
            }, pagarmeTdsNx.TIMEOUT_MS);

            request = jQuery.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/wc-api/pagarme-tds-token-nx',
                async: true,
                cache: false,
                success: (response) => {
                    clearTimeout(timeout);
                    resolve({
                        token: response?.data?.token || null,
                        flowPreference:
                            response?.data?.flow_preference ||
                            pagarmeTdsNx.LEGACY_FLOW,
                    });
                },
                error: () => {
                    clearTimeout(timeout);
                    resolve({
                        token: null,
                        flowPreference: pagarmeTdsNx.LEGACY_FLOW,
                    });
                },
            });
        });
    },

    /**
     * @param {string} url
     * @param {string} globalName Global exposto pelo SDK (`tifa`, `ThreeDS`).
     */
    loadScriptAsync: (url, globalName) => {
        return new Promise((resolve, reject) => {
            if (!url) {
                reject(new Error(`Missing SDK url for ${globalName}`));
                return;
            }

            if (typeof window !== 'undefined' && typeof window[globalName] !== 'undefined') {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = url;
            script.async = true;

            const timeout = setTimeout(() => {
                script.remove();
                reject(new Error(`Script timeout loading ${url}`));
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

    executeNxFlow: (nxToken, tdsData) => {
        return new Promise((resolve, reject) => {
            if (typeof window.tifa === 'undefined') {
                reject(new Error('Tifa SDK not loaded'));
                return;
            }

            let settled = false;
            let timeout = null;
            const settle = (callback, payload) => {
                if (settled) {
                    return;
                }
                settled = true;
                clearTimeout(timeout);
                callback(payload);
            };

            timeout = setTimeout(() => {
                settle(reject, new Error('3DS-NX authentication timeout'));
            }, pagarmeTdsNx.TIMEOUT_MS);

            try {
                window.tifa.init(
                    {
                        token: nxToken,
                        container: pagarmeTdsNx.containerTarget,
                        challengeContainer: pagarmeTdsNx.challengeTarget,
                    },
                    tdsData,
                    (result) => settle(resolve, result),
                    (error) => settle(reject, error)
                );
            } catch (error) {
                settle(reject, error);
            }
        });
    },

    formatNxResponse: (nxResult) => {
        return {
            risk_id: nxResult.risk_id || nxResult.riskId,
            steps: [
                {
                    tds_server_trans_id:
                        nxResult.tds_server_trans_id || nxResult.transactionId,
                    trans_status: pagarmeTdsNx.getTransStatus(nxResult),
                    authenticated_card:
                        nxResult.authenticated_card || nxResult.card,
                    challenge_canceled: pagarmeTdsNx.isChallengeCanceled(nxResult),
                },
            ],
            _flow_type: pagarmeTdsNx.NX_FLOW,
        };
    },

    getTransStatus: (nxResult) => {
        return nxResult?.trans_status || nxResult?.transStatus || '';
    },

    isChallengeCanceled: (nxResult) => {
        return !!(nxResult?.challenge_canceled || nxResult?.challengeCanceled);
    },

    /**
     * @returns {Promise<{status: string, authentication?: object, errorKey?: string}>}
     */
    attempt: async () => {
        let nxResult;

        try {
            const { token, flowPreference } = await pagarmeTdsNx.getTdsTokenNx();
            if (!token || flowPreference !== pagarmeTdsNx.NX_FLOW) {
                return { status: pagarmeTds.STATUS_UNAVAILABLE };
            }

            const cardExpiryDate = pagarmeTds.getCardExpiryDate();
            if (!cardExpiryDate) {
                return {
                    status: pagarmeTds.STATUS_DENIED,
                    errorKey: pagarmeTds.FAIL_ASSEMBLE_CARD_EXPIRY_DATE,
                };
            }

            const urls = pagarmeTdsNx.getSdkUrls();
            await pagarmeTdsNx.loadScriptAsync(urls.tifa, 'tifa');
            await pagarmeTdsNx.loadScriptAsync(urls.nx, 'ThreeDS');

            nxResult = await pagarmeTdsNx.executeNxFlow(
                token,
                pagarmeTds.getTdsData('02', cardExpiryDate)
            );
        } catch (error) {
            // Falha técnica antes de o NX responder: o fallback é decisão do pagarmeTds.
            return { status: pagarmeTds.STATUS_UNAVAILABLE };
        }

        // A partir daqui o NX RESPONDEU: o resultado é terminal e nunca volta a 'unavailable'.
        if (!nxResult || pagarmeTdsNx.isChallengeCanceled(nxResult)) {
            return {
                status: pagarmeTds.STATUS_DENIED,
                errorKey: pagarmeTdsToken.FAIL_GET_TOKEN,
            };
        }

        if (!pagarmeTdsNx.getTransStatus(nxResult)) {
            return {
                status: pagarmeTds.STATUS_DENIED,
                errorKey: pagarmeTdsToken.FAIL_GET_TOKEN,
            };
        }

        return {
            status: pagarmeTds.STATUS_AUTHENTICATED,
            authentication: pagarmeTdsNx.formatNxResponse(nxResult),
        };
    },
};
