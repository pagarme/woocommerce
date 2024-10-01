const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

import PropTypes from "prop-types";
import Card from "../Card";
import useCreditCard from "./useCreditCard";

const backendConfig = wc.wcSettings.getSetting(
    "woo-pagarme-payments-credit_card_data",
);

const PagarmeCreditCardComponent = (props) => {
    const { emitResponse, eventRegistration } = props;
    useCreditCard(backendConfig, emitResponse, eventRegistration);

    return (
        <Card {...props} backendConfig={backendConfig} cardIndex={1} />
    );
};

const PagarmeCreditCardLabel = ({ components }) => {
    const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={backendConfig.label} />;
};

PagarmeCreditCardComponent.propTypes = {
    emitResponse: PropTypes.object,
    eventRegistration: PropTypes.object,
};

PagarmeCreditCardLabel.propTypes = {
    components: PropTypes.object,
};

const pagarmeCreditCardPaymentMethod = {
    name: backendConfig.name,
    label: <PagarmeCreditCardLabel />,
    content: <PagarmeCreditCardComponent />,
    edit: <PagarmeCreditCardComponent />,
    canMakePayment: () => true,
    ariaLabel: backendConfig.ariaLabel,
};

registerPaymentMethod(pagarmeCreditCardPaymentMethod);
