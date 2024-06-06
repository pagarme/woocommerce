import { createReduxStore, register } from "@wordpress/data";

const DEFAULT_CARD = {
    holderName: "",
    number: "",
    expirationDate: "",
    installment: 1,
    brand: "",
    cvv: "",
    saveCard: false,
    walletId: "",
    errors: {},
};

const DEFAULT_STATE = {
    cards: {
        1: {
            ...DEFAULT_CARD,
        },
        2: {
            ...DEFAULT_CARD,
        },
    },
};

const actions = {
    setHolderName(cardIndex, holderName) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: holderName,
            propertyName: "holderName",
        };
    },
    setNumber(cardIndex, number) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: number,
            propertyName: "number",
        };
    },
    setExpirationDate(cardIndex, expirationDate) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: expirationDate,
            propertyName: "expirationDate",
        };
    },
    setInstallment(cardIndex, installment) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: installment,
            propertyName: "installment",
        };
    },
    setBrand(cardIndex, brand) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: brand,
            propertyName: "brand",
        };
    },
    setCvv(cardIndex, cvv) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: cvv,
            propertyName: "cvv",
        };
    },
    setSaveCard(cardIndex, saveCard) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: saveCard,
            propertyName: "saveCard",
        };
    },
    setWalletId(cardIndex, walletId) {
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: walletId,
            propertyName: "walletId",
        };
    },
    setErrors(cardIndex, errors){
        return {
            type: "SET_PROPERTY_VALUE",
            cardIndex,
            value: errors,
            propertyName: "errors",
        };
    },
    reset() {
        return {
            type: "RESET",
        };
    }
};

const pagarmeCardsStore = createReduxStore("pagarme-cards", {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case "SET_PROPERTY_VALUE":
                if (action.propertyName?.length === 0) {
                    return state;
                }

                return {
                    ...state,
                    cards: {
                        ...state.cards,
                        [action.cardIndex]: {
                            ...state.cards[action.cardIndex],
                            [action.propertyName]: action.value,
                        },
                    },
                };
            case "RESET":
                return DEFAULT_STATE;
        }

        return state;
    },

    actions,

    selectors: {
        getHolderName(state, cardIndex) {
            return state.cards[cardIndex].holderName;
        },
        getNumber(state, cardIndex) {
            return state.cards[cardIndex].number;
        },
        getExpirationDate(state, cardIndex) {
            return state.cards[cardIndex].expirationDate;
        },
        getInstallment(state, cardIndex) {
            return state.cards[cardIndex].installment;
        },
        getBrand(state, cardIndex) {
            return state.cards[cardIndex].brand;
        },
        getCvv(state, cardIndex) {
            return state.cards[cardIndex].cvv;
        },
        getSaveCard(state, cardIndex) {
            return state.cards[cardIndex].saveCard;
        },
        getWalletId(state, cardIndex) {
            return state.cards[cardIndex].walletId;
        },
        getCards(state) {
            return state.cards;
        },
        getErrors(state, cardIndex) {
            return state.cards[cardIndex].errors;
        },
    },
});

register(pagarmeCardsStore);

export default pagarmeCardsStore;
