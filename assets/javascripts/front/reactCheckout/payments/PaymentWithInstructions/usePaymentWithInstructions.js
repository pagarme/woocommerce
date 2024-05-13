import { useEffect } from "@wordpress/element";

const usePaymentWithInstructions = (
    emitResponse,
    eventRegistration,
    backendConfig,
) => {
    const { onPaymentSetup } = eventRegistration;

    useEffect(() => {
        return onPaymentSetup(() => {
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
    }, [onPaymentSetup, backendConfig]);
};

export default usePaymentWithInstructions;
