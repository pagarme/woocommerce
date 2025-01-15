import PaymentWithInstructions from "../PaymentWithInstructions";
import PropTypes from "prop-types";

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting("woo-pagarme-payments-pix_data");

const PagarmePixComponent = (props) => {
    return <PaymentWithInstructions {...props} backendConfig={backendConfig} />;
};

const PagarmePixLabel = ({ components }) => {
    const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={backendConfig.label} />;
};

PagarmePixLabel.propTypes = {
    components: PropTypes.object,
};

const pagarmePixPaymentMethod = {
    name: backendConfig.name,
    label: <PagarmePixLabel />,
    content: <PagarmePixComponent />,
    edit: <PagarmePixComponent />,
    canMakePayment: () => true,
    ariaLabel: backendConfig.ariaLabel,
    supports: {
        features: [
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions'
        ],
    }
};

registerPaymentMethod(pagarmePixPaymentMethod);
