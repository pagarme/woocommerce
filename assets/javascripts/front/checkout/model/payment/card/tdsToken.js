/* globals jQuery */
/* jshint esversion: 11 */

/**
 * Token do 3DS legado. Assíncrono de propósito: o `async: false` anterior travava a
 * main thread e fazia a requisição legada se sobrepor à do 3DS-NX.
 */
const pagarmeTdsToken = {
    FAIL_GET_TOKEN: "fail_get_token",
    TIMEOUT_MS: 30000,

    /**
     * @returns {Promise<{token?: string, error?: string}>}
     */
    getToken: () => {
        return new Promise((resolve) => {
            let request;

            const timeout = setTimeout(() => {
                if (request) {
                    request.abort();
                }
                resolve({ error: pagarmeTdsToken.FAIL_GET_TOKEN });
            }, pagarmeTdsToken.TIMEOUT_MS);

            request = jQuery.ajax({
                type: "GET",
                dataType: "json",
                url: "/wc-api/pagarme-tds-token",
                async: true,
                cache: false,
                success: (response) => {
                    clearTimeout(timeout);
                    const token = response?.data?.token;
                    if (!token) {
                        resolve({ error: pagarmeTdsToken.FAIL_GET_TOKEN });
                        return;
                    }
                    resolve({ token: token });
                },
                error: () => {
                    clearTimeout(timeout);
                    resolve({ error: pagarmeTdsToken.FAIL_GET_TOKEN });
                },
            });
        });
    },
};
