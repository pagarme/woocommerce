/* globals Script3ds */
/* jshint esversion: 11 */
const initTds = {
    challengeWindowSize: '03',
    modalTarget: '#challengeIframeElement',
    methodContainerTarget: '#tdsMethodContainer',
    challengeContainerTarget: '#challengeContainer',
    SDK_UNAVAILABLE: 'serviceUnavailable',

    callTdsFunction(tdsToken, tdsData, callbackTds) {
        initTds.getSdkReady()
            .then((engine) => initTds.run(engine, tdsToken, tdsData, callbackTds))
            .catch((error) => {
                console.error('[Pagar.me 3DS] Falha ao iniciar a autenticação.', error);
                callbackTds({ error: initTds.SDK_UNAVAILABLE });
            });
    },

    /**
     * Os SDKs são injetados de forma assíncrona pelo template, então esperamos o
     * handle de readiness antes de autenticar. Quando ele não existe (template
     * não renderizado ou script inline bloqueado) caímos na detecção direta.
     */
    getSdkReady() {
        if (window.pagarmeTdsSdkReady) {
            return window.pagarmeTdsSdkReady;
        }
        return Promise.resolve(window.pagarmeTdsEngine || initTds.detectEngine());
    },

    detectEngine() {
        if (initTds.getTifaSdk()) {
            return 'tifa';
        }
        if (initTds.getLegacySdk()) {
            return 'legacy';
        }
        return null;
    },

    getTifaSdk() {
        const sdk = window.Tifa || window.tifa;
        return sdk && typeof sdk.authenticate === 'function' ? sdk : null;
    },

    getLegacySdk() {
        let sdk = window.Script3ds;
        if (!sdk && typeof Script3ds !== 'undefined') {
            sdk = Script3ds;
        }
        return sdk && typeof sdk.init3ds === 'function' ? sdk : null;
    },

    run(engine, tdsToken, tdsData, callbackTds) {
        const resolvedEngine = engine || initTds.detectEngine();

        if (resolvedEngine === 'tifa') {
            initTds.runTifa(tdsToken, tdsData, callbackTds);
            return;
        }

        if (resolvedEngine === 'legacy') {
            initTds.runLegacy(tdsToken, tdsData, callbackTds);
            return;
        }

        console.error('[Pagar.me 3DS] Nenhum SDK de 3DS disponível para autenticar.');
        callbackTds({ error: initTds.SDK_UNAVAILABLE });
    },

    /**
     * O bundle legado controla a exibição de #challengeIframeElement por conta própria.
     */
    runLegacy(tdsToken, tdsData, callbackTds) {
        initTds.getLegacySdk().init3ds(
            tdsToken,
            tdsData,
            callbackTds,
            initTds.challengeWindowSize
        );
    },

    runTifa(tdsToken, tdsData, callbackTds) {
        const sdk = initTds.getTifaSdk();
        const observer = initTds.observeChallenge();
        const finish = (result) => {
            if (observer) {
                observer.disconnect();
            }
            initTds.hideChallengeModal();
            callbackTds(result);
        };

        sdk.authenticate({
            token: tdsToken,
            data: tdsData,
            methodContainer: initTds.methodContainerTarget,
            challengeContainer: initTds.challengeContainerTarget,
            onComplete: finish,
            onError: (error) => finish({ error }),
        });
    },

    /**
     * O modal só é aberto quando o SDK realmente injeta o desafio no container,
     * para não exibir um overlay vazio em autenticações frictionless.
     */
    observeChallenge() {
        const container = document.querySelector(initTds.challengeContainerTarget);
        if (!container || typeof MutationObserver === 'undefined') {
            return null;
        }

        const observer = new MutationObserver(() => {
            if (container.childElementCount > 0) {
                initTds.showChallengeModal();
            }
        });
        observer.observe(container, { childList: true, subtree: true });

        return observer;
    },

    showChallengeModal() {
        const modal = document.querySelector(initTds.modalTarget);
        if (modal) {
            modal.style.display = 'block';
        }
    },

    hideChallengeModal() {
        const modal = document.querySelector(initTds.modalTarget);
        if (modal) {
            modal.style.display = 'none';
        }
    },
};
