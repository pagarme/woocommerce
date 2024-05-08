import PaymentWithInstructions from "../PaymentWithInstructions";
import PropTypes from "prop-types";

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting(
    "woo-pagarme-payments-billet_data",
);

const PagarmeBilletComponent = (props) => {
    return <PaymentWithInstructions {...props} backendConfig={backendConfig} />;
};

const PagarmeBilletLabel = ({ components }) => {
    const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={backendConfig.label} />;
};

PagarmeBilletLabel.propTypes = {
    components: PropTypes.object,
};

const pagarmeBilletPaymentMethod = {
    name: backendConfig.name,
    label: <PagarmeBilletLabel />,
    content: <PagarmeBilletComponent />,
    edit: <PagarmeBilletComponent />,
    canMakePayment: () => true,
    ariaLabel: backendConfig.ariaLabel,
};

registerPaymentMethod(pagarmeBilletPaymentMethod);
