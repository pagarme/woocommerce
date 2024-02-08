import PaymentWithInstructions from '../components/payment-with-instructions';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting('woo-pagarme-payments-billet_data');

const PagarmeBilletComponent = (props) => {
	return (
        <PaymentWithInstructions {...props} backendConfig={backendConfig} />
    );
};

const PagarmeBilletCardLabel = ( { components } ) => {
	const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={ backendConfig.label } />;
}


const pagarmeBilletPaymentMethod = {
	name: backendConfig.name,
	label: <PagarmeBilletCardLabel />,
	content: <PagarmeBilletComponent />,
	edit: <PagarmeBilletComponent />,
	canMakePayment: () => true,
	ariaLabel: backendConfig.ariaLabel
};

registerPaymentMethod(pagarmeBilletPaymentMethod);