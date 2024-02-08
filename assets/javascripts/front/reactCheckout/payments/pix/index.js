import PaymentWithInstructions from '../components/payment-with-instructions';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting('woo-pagarme-payments-pix_data');

const PagarmePixComponent = (props) => {
	return (
        <PaymentWithInstructions {...props} backendConfig={backendConfig} />
    );
};

const PagarmePixCardLabel = ( { components } ) => {
	const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={ backendConfig.label } />;
}


const pagarmePixPaymentMethod = {
	name: backendConfig.name,
	label: <PagarmePixCardLabel />,
	content: <PagarmePixComponent />,
	edit: <PagarmePixComponent />,
	canMakePayment: () => true,
	ariaLabel: backendConfig.ariaLabel
};

registerPaymentMethod(pagarmePixPaymentMethod);