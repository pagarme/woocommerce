/* jshint esversion: 11 */
const initTds = {
    SDK_UNAVAILABLE: 'serviceUnavailable',
    CHALLENGE_CANCELED: 'challengeCanceled',
    methodContainerId: 'tdsMethodContainer',
    challengeContainerId: 'challengeContainer',
    methodContainerId: 'tdsMethodContainer',
    challengeContainerId: 'challengeContainer',

    callTdsFunction(tokenData, orderData, callbackTds) {
        const token = tokenData?.token;

        initTds.loadTds()
            .then(() => initTds.run(token, orderData))
            .then((result) => callbackTds(result))
            .catch((error) => {
                console.error('[Pagar.me 3DS] Falha ao executar a autenticação.', error);
                callbackTds({ error: initTds.SDK_UNAVAILABLE });
            });
    },

    loadTds() {
        if (!window.pagarmeTds3ds) {
            return Promise.reject(
                new Error('Loader de 3DS indisponível: o template não foi renderizado.')
            );
        }
        return window.pagarmeTds3ds.load();
    },

    run(token, orderData) {
        return initTds.runTds(token, orderData);
    },

    runTds(token, orderData) {
        const tdsMethodContainerElement = document.getElementById(initTds.methodContainerId);
        const challengeContainerElement = document.getElementById(initTds.challengeContainerId);

        if (!tdsMethodContainerElement || !challengeContainerElement) {
            return Promise.reject(
                new Error('Containers do 3DS não encontrados no DOM.')
            );
        }

        return window.TDS.init({
            token,
            tdsMethodContainerElement,
            challengeContainerElement,
        }, orderData).then(initTds.normalizeTdsResponse);
    },

    normalizeTdsResponse(response) {
        const tds = response?.[0];

        if (!tds) {
            console.error('[Pagar.me 3DS] Resposta do 3DS sem dados esperados.', response);
            return { error: initTds.SDK_UNAVAILABLE };
        }

        if (tds.challenge_canceled) {
            return { error: initTds.CHALLENGE_CANCELED };
        }

        return {
            trans_status: tds.trans_status,
            tds_server_trans_id: tds.tds_server_trans_id,
        };
    },
};
