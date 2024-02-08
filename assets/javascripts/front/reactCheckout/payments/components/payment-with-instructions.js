import { useEffect } from '@wordpress/element';

const PaymentWithInstructions = ({ emitResponse, eventRegistration, backendConfig }) => {
	
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
				<img className='logo' src={backendConfig.logo} alt={backendConfig.label} title={backendConfig.label} />
			</p>
		</>
	);
};

export default PaymentWithInstructions;