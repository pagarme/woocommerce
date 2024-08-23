import PropTypes from "prop-types";
import GooglePayButton from "@google-pay/button-react";
import useGooglepay from "./useGooglepay";
import { useDispatch, useSelect } from "@wordpress/data";
import pagarmeTokenStore from "../store/googlepay"

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting(
    "woo-pagarme-payments-googlepay_data",
);

let googleResponse = [];
const PagarmeGooglePayComponent = (props) => {
    // console.log(props)
    const { emitResponse, eventRegistration } = props;

    useGooglepay(emitResponse, eventRegistration, backendConfig);

    const {
        setToken
    } = useDispatch(pagarmeTokenStore);


    return (
        <GooglePayButton
           
            environment="TEST"
            buttonLocale="pt"
            buttonType="pay"
            paymentRequest={{
                apiVersion: 2,
                apiVersionMinor: 0,
                allowedPaymentMethods: [
                    {
                        type: "CARD",
                        parameters: {
                            allowedAuthMethods: ["PAN_ONLY"],
                            allowedCardNetworks: ["MASTERCARD", "VISA", "ELO"],
                        },
                        tokenizationSpecification: {
                            type: "PAYMENT_GATEWAY",
                            parameters: {
                                gateway: 'pagarme',
                                gatewayMerchantId: backendConfig.accountId,
                            },
                        },
                    },
                ],
                merchantInfo: {
                    merchantId: backendConfig.merchantId,
                    merchantName: backendConfig.merchantName,
                },
                transactionInfo: {
                    totalPriceStatus: "FINAL",
                    totalPriceLabel: "Total",
                    totalPrice: (props.billing.cartTotal.value / 100).toString(),
                    currencyCode: "BRL",
                    countryCode: "BR",
                },
            }}
            onLoadPaymentData={(paymentRequest) => {
                let googleToken = paymentRequest.paymentMethodData.tokenizationData.token;
                setToken(googleToken);
            }}
        />
    );
    
};

const PagarmeGooglePayLabel = ({ components }) => {
    const { PaymentMethodLabel } = components;
    return <PaymentMethodLabel text={backendConfig.label} />;
};

PagarmeGooglePayComponent.propTypes = {
    emitResponse: PropTypes.object,
    eventRegistration: PropTypes.object,
};

PagarmeGooglePayLabel.propTypes = {
    components: PropTypes.object,
};


const pagarmeGooglePayPaymentMethod = {
    name: backendConfig.name,
    label: <PagarmeGooglePayLabel />,
    content: <PagarmeGooglePayComponent />,
    edit: <PagarmeGooglePayComponent />,
    canMakePayment: () => true,
    ariaLabel: backendConfig.ariaLabel,
};

registerPaymentMethod(pagarmeGooglePayPaymentMethod);
