/* jshint esversion: 11 */
const initTds = {
    ENGINE_NX: 'nx',
    ENGINE_LEGACY: 'legacy',
    SDK_UNAVAILABLE: 'serviceUnavailable',
    CHALLENGE_CANCELED: 'challengeCanceled',
    challengeWindowSize: '03',
    methodContainerId: 'tdsMethodContainer',
    challengeContainerId: 'challengeContainer',

    /**
     * O engine vem do backend junto com o token, porque só o emissor do token
     * define qual SDK sabe usá-lo. Não há fallback entre engines: mandar um
     * token NX para o bundle legado resulta em 401 no `pre-auth`.
     */
    callTdsFunction(tokenData, orderData, callbackTds) {
        const engine = tokenData?.engine;
        const token = tokenData?.token;

        initTds.loadEngine(engine)
            .then(() => initTds.run(engine, token, orderData))
            .then((result) => callbackTds(result))
            .catch((error) => {
                console.error('[Pagar.me 3DS] Falha ao executar a autenticação.', error);
                callbackTds({ error: initTds.SDK_UNAVAILABLE });
            });
    },

    /**
     * O loader é publicado pelo template, que conhece as URLs de cada ambiente.
     */
    loadEngine(engine) {
        if (!window.pagarmeTds3ds) {
            return Promise.reject(
                new Error('Loader de 3DS indisponível: o template não foi renderizado.')
            );
        }
        return window.pagarmeTds3ds.load(engine);
    },

    run(engine, token, orderData) {
        if (engine === initTds.ENGINE_NX) {
            return initTds.runNx(token, orderData);
        }
        if (engine === initTds.ENGINE_LEGACY) {
            return initTds.runLegacy(token, orderData);
        }
        return Promise.reject(new Error(`Engine de 3DS desconhecido: ${engine}`));
    },

    /**
     * A lib do AuthSwitch orquestra fingerprint e 3DS e devolve uma Promise; ela
     * exige os elementos dos containers, não seletores.
     */
    runNx(token, orderData) {
        const tdsMethodContainerElement = document.getElementById(initTds.methodContainerId);
        const challengeContainerElement = document.getElementById(initTds.challengeContainerId);

        if (!tdsMethodContainerElement || !challengeContainerElement) {
            return Promise.reject(
                new Error('Containers do 3DS NX não encontrados no DOM.')
            );
        }

        return window.tifa.init({
            tds: {
                token,
                orderData,
                tdsMethodContainerElement,
                challengeContainerElement,
            },
        }).then(initTds.normalizeNxResponse);
    },

    /**
     * O AuthSwitch agrupa o resultado por produto e devolve o 3DS em um array,
     * enquanto o resto do checkout trabalha com o formato plano do 3DS legado.
     */
    normalizeNxResponse(response) {
        const tds = response?.steps?.tds?.[0];

        if (!tds) {
            console.error('[Pagar.me 3DS] Resposta do AuthSwitch sem dados de 3DS.', response);
            return { error: initTds.SDK_UNAVAILABLE };
        }

        if (tds.challenge_canceled) {
            return { error: initTds.CHALLENGE_CANCELED };
        }

        return {
            trans_status: tds.trans_status,
            tds_server_trans_id: tds.tds_server_trans_id,
            risk_id: response.risk_id,
        };
    },

    /**
     * O bundle legado é baseado em callback e controla a exibição de
     * #challengeIframeElement por conta própria.
     */
    runLegacy(token, orderData) {
        return new Promise((resolve) => {
            window.Script3ds.init3ds(
                token,
                orderData,
                resolve,
                initTds.challengeWindowSize
            );
        });
    },
};
