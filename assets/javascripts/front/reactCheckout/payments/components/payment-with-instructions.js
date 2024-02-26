import { useEffect } from '@wordpress/element';
import PropTypes from 'prop-types';

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

PaymentWithInstructions.propTypes = {
	emitResponse: PropTypes.object.isRequired,
	eventRegistration: PropTypes.object.isRequired,
	backendConfig: PropTypes.object.isRequired,
};


export default PaymentWithInstructions;