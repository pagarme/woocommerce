/* jshint esversion: 9 */
import pagarmeTokenStore from "../store/googlepay";
import { useEffect } from "@wordpress/element";
import { useDispatch, useSelect } from "@wordpress/data";

const useGooglepay = (
    emitResponse,
    eventRegistration,
    backendConfig
) => {
    const { reset } = useDispatch(pagarmeTokenStore);

    const { onPaymentSetup } = eventRegistration;
    
    const cards = useSelect((select) => {
        return select(pagarmeTokenStore).getToken();
    });

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
                            [backendConfig.key]: {[backendConfig.key]: {['payload']: cards}}
                        }),
                        payment_method: backendConfig.key,
                    },
                },
            };
        });
    }, [onPaymentSetup, cards, backendConfig]);
};
export default useGooglepay;
