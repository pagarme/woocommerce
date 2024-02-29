import { useEffect } from "@wordpress/element";

const usePaymentWithInstructions = (
    emitResponse,
    eventRegistration,
    backendConfig,
) => {
    const { onPaymentSetup } = eventRegistration;

    useEffect(() => {
        const unsubscribe = onPaymentSetup(() => {
            const paymentMethodData = {
                payment_method: backendConfig.key,
            };

            return {
                type: emitResponse.responseTypes.SUCCESS,
                meta: {
                    paymentMethodData,
                },
            };
        });

        return unsubscribe;
    }, [onPaymentSetup, backendConfig]);
};

export default usePaymentWithInstructions;
