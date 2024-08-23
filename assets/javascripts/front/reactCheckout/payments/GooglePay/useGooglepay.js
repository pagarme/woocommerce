/* jshint esversion: 9 */
import pagarmeTokenssStore from "../store/googlepay";
import { useEffect } from "@wordpress/element";
import { useDispatch, useSelect } from "@wordpress/data";

const useGooglepay = (
    emitResponse,
    eventRegistration,
    backendConfig
) => {
    const { reset } = useDispatch(pagarmeTokenssStore);

    const { onPaymentSetup } = eventRegistration;
    
    const cards = useSelect((select) => {
        return select(pagarmeTokenssStore).getToken();
    });

    useEffect(() => {
        reset();
    }, []);
    
    useEffect(() => {
        return onPaymentSetup(() => {
            const paymentMethodData = {
                payment_method: backendConfig.key,
            };

            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData: {
                        pagarme: JSON.stringify({
                            [backendConfig.key]: {
                                googleData: {
                                    cards,
                                },
                            },
                        }),
                        payment_method: backendConfig.key,
                    },
                },
            };
        });
    }, [onPaymentSetup, backendConfig]);
};
export default useGooglepay;
