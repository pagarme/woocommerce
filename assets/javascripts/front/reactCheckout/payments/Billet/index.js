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

registerPaymentMethod(pagarmeBilletPaymentMethod);
