import GooglePayButton from "@google-pay/button-react";
import { useDispatch } from "@wordpress/data";
import pagarmeTokenStore from "../store/googlepay"
import validateBilling from "./validateBilling";

const PagarmeGooglePayComponent = (props) => {
    const backendConfig = wc.wcSettings.getSetting(
        "woo-pagarme-payments-googlepay_data",
    );
    
    const environment = backendConfig.isSandboxMode ? "TEST" : "PRODUCTION";
    const billingAddress = props?.billing?.billingAddress;
    const {
        setToken
    } = useDispatch(pagarmeTokenStore);

    return (
        
        <GooglePayButton
            environment={environment}
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
                            allowedCardNetworks: backendConfig.allowedGoogleBrands,
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
                if(validateBilling(billingAddress)) {
                    let googleToken = paymentRequest.paymentMethodData.tokenizationData.token;
                    setToken(googleToken);
                }
                jQuery(".wc-block-components-checkout-place-order-button").click();
            }}
        />
    );
    
};
export default PagarmeGooglePayComponent;