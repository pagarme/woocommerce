const initTds = {
    callTdsFunction(tdsToken, tdsData, callbackTds) {
        if (typeof window.Tifa === 'undefined') {
            callbackTds({ error: 'Tifa SDK not loaded' });
            return;
        }

        window.Tifa.authenticate({
            token: tdsToken,
            data: tdsData,
            methodContainer: '#tdsMethodContainer',
            challengeContainer: '#challengeContainer',
            onComplete: (result) => {
                callbackTds(result);
            },
            onError: (error) => {
                callbackTds({ error });
            }
        });
    },
};
