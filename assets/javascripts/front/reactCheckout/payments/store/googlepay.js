// pagarmeTokenssStore.js

import { createReduxStore, register } from "@wordpress/data";

const DEFAULT_CARD = {
    token: "",
    errors: {}
};

const DEFAULT_STATE = {
    ...DEFAULT_CARD
};

const actions = {
    setToken(token) {
        return {
            type: "SET_PROPERTY_VALUE",
            value: token,
            propertyName: "token",
        };
    },
    
    setErrors(errors){
        return {
            type: "SET_PROPERTY_VALUE",
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

const pagarmeTokenssStore = createReduxStore("pagarme-googlepay", {
    reducer(state = DEFAULT_STATE, action) {
        console.log(state);
        switch (action.type) {
            case "SET_PROPERTY_VALUE":
                if (!action.propertyName) {
                    return state;
                }

                return {
                    ...state,
                    [action.propertyName]: action.value,
                };
            case "RESET":
                return DEFAULT_STATE;
            default:
                return state;
        }
    },

    actions,

    selectors: {
        getToken(state) {
            return state.cards;
        },
        getErrors(state) {
            return state.cards.errors;
        },
    },
});

register(pagarmeTokenssStore);

export default pagarmeTokenssStore;