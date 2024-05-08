import PropTypes from "prop-types";
import usePaymentWithInstructions from "./usePaymentWithInstructions";

const PaymentWithInstructions = ({
    emitResponse,
    eventRegistration,
    backendConfig,
}) => {
    usePaymentWithInstructions(emitResponse, eventRegistration, backendConfig);

    return (
        <>
            <p className="pagarme-payment-method-instructions">
                {backendConfig.instructions}
            </p>
            <p className="pagarme-payment-method-logo">
                <img
                    className="logo"
                    src={backendConfig.logo}
                    alt={backendConfig.label}
                    title={backendConfig.label}
                />
            </p>
        </>
    );
};

PaymentWithInstructions.propTypes = {
    emitResponse: PropTypes.object.isRequired,
    eventRegistration: PropTypes.object.isRequired,
    backendConfig: PropTypes.object.isRequired,
};

export default PaymentWithInstructions;
