import { useEffect } from '@wordpress/element';

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const backendConfig = wc.wcSettings.getSetting('woo-pagarme-payments-pix_data');

const PagarmePixComponent = ({ emitResponse, eventRegistration }) => {
	
	const { onPaymentSetup } = eventRegistration;

	useEffect( () => {
		const unsubscribe = onPaymentSetup( () => {
			const paymentMethodData = {
				payment_method: backendConfig.key
			}

			return {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData
				}
			};
		});

		return unsubscribe;
	}, [ onPaymentSetup, backendConfig ] );

	return (
		<>
			<p className='pagarme-payment-method-instructions'>{backendConfig.instructions}</p>
			<p>
				<img className='logo' src={backendConfig.logo} alt='Pix' title='Pix' />
			</p>
		</>
	);
};

const PagarmePixCardLabel = ( { components } ) => {
	const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={ backendConfig.label } />;
}


const pagarmeCreditCardPaymentMethod = {
	name: backendConfig.name,
	label: <PagarmePixCardLabel />,
	content: <PagarmePixComponent />,
	edit: <PagarmePixComponent />,
	canMakePayment: () => true,
	ariaLabel: backendConfig.ariaLabel
};

registerPaymentMethod(pagarmeCreditCardPaymentMethod);