// pagarmeTokenStore.js

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

const pagarmeTokenStore = createReduxStore("pagarme-googlepay", {
    reducer(state = DEFAULT_STATE, action) {
        switch (action.type) {
            case "SET_PROPERTY_VALUE":
                // console.log(action);
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
            return state.token;
        },
        getErrors(state) {
            return state.errors;
        },
    },
});

register(pagarmeTokenStore);

export default pagarmeTokenStore;