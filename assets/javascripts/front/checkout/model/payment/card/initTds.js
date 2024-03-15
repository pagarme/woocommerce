const initTds = {
    callTdsFunction(tdsToken, tdsData, callbackTds) {
        const challengeWindowSize = "03";
        Script3ds.init3ds(tdsToken, tdsData, callbackTds, challengeWindowSize);
    },
};
