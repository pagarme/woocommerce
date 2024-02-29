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
        const unsubscribe = onPaymentSetup(async () => {
            try {
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

        return unsubscribe;
    }, [onPaymentSetup, cards, backendConfig]);
};

export default useCreditCard;
