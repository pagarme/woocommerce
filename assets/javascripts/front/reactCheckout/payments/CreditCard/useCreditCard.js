/* jshint esversion: 9 */
import pagarmeCardsStore from "../store/cards";
import TokenizeException from "../Card/token/tokenizeException";
import tokenizeMultiCards from "../Card/token/tokenizeMultiCards";
import { useDispatch, useSelect } from "@wordpress/data";
import { useEffect } from "@wordpress/element";

const useCreditCard = (backendConfig, emitResponse, eventRegistration) => {
    const { reset } = useDispatch(pagarmeCardsStore);

    const { onPaymentSetup } = eventRegistration;

    const cards = useSelect((select) => {
        return select(pagarmeCardsStore).getCards();
    });

    useEffect(() => {
        reset();
    }, []);

    useEffect(() => {
        return onPaymentSetup(async () => {
            try {
                let hasErrors = false;
                if (typeof cards === 'object') {
                    hasErrors = Object.values(cards).some((card) => {
                        return Object.keys(card.errors).length > 0;
                    });
                }
                if (hasErrors) {
                    return {
                        type: emitResponse.responseTypes.ERROR,
                        message: backendConfig.errorMessages.creditCardFormHasErrors,
                    };
                }
                const formatedCards = await tokenizeMultiCards(
                    cards,
                    1,
                    backendConfig,
                );

                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            pagarme: JSON.stringify({
                                [backendConfig.key]: {
                                    cards: {
                                        ...formatedCards,
                                    },
                                },
                            }),
                            payment_method: backendConfig.key,
                        },
                    },
                };
            } catch (e) {
                let errorMesage =
                    backendConfig.errorMessages.serviceUnavailable;
                if (e instanceof TokenizeException) {
                    errorMesage = e.message;
                }

                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: errorMesage,
                };
            }
        });
    }, [onPaymentSetup, cards, backendConfig]);
};

export default useCreditCard;
