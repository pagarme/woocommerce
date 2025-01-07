const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

import PropTypes from "prop-types";
import Card from "../Card";
import PagarmeGooglePayComponent from "../GooglePay";
import useCreditCard from "./useCreditCard";
import pagarmeTokenStore from "../store/googlepay";
import { useSelect } from "@wordpress/data";

const backendConfig = wc.wcSettings.getSetting(
    "woo-pagarme-payments-credit_card_data",
);
const googlePayBackendConfig = wc.wcSettings.getSetting(
    "woo-pagarme-payments-googlepay_data",
);

const PagarmeCreditCardComponent = (props) => {
    const googleCards = useSelect((select) => {
        return select(pagarmeTokenStore).getToken();
    });
    const { emitResponse, eventRegistration } = props;
    const googleActive = googlePayBackendConfig.enabled;
    const hasSubscriptionInCart = googlePayBackendConfig.hasSubscriptionInCart;

    useCreditCard(backendConfig, emitResponse, eventRegistration, googleCards);
    return (
        <div>
            {googleActive && !hasSubscriptionInCart && (
                <div>
                    <PagarmeGooglePayComponent  {...props}  />
                    <div className="pagarme_creditcard_divider">
                        <p>Ou pague com cartão</p>
                    </div>
                </div>
            )}
            {!googleCards && (
                <Card {...props} backendConfig={backendConfig} cardIndex={1} />
            )}
        </div>
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

registerPaymentMethod(pagarmeCreditCardPaymentMethod);
