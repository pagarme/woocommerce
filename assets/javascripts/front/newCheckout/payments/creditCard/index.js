import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { tokenize } from './tokenize';
const { Button } = window.wc.blocksCheckout;

const { registerPaymentMethod } = window.wc.wcBlocksRegistry;


const PagarmeCrediCardComponent = ( props ) => {

	const backendConfig = wc.wcSettings.getSetting('woo-pagarme-payments-credit_card_data');

	console.log('backendConfig', backendConfig)

	const { emitResponse, eventRegistration, billing } = props;

	const { onCheckoutValidation, onPaymentSetup } = eventRegistration;

	const { cartTotal } = billing;

	const [cardNumber, setCardNumber ] = useState('4000 0000 0000 0010');
	const [cardHolderName, setCardHolderName ] = useState('Teste teste');
	const [cardExpirationDate, setCardExpirationDate ] = useState('11/24');
	const [cardCvv, setCardCvv ] = useState('123');
	const [cardToken, setCardToken ] = useState({});
	const [cardPrice, setCardPrice ] = useState('');

	const cardNumberInputChangeHandler = event => {
		setCardNumber(event.target.value);
	}

	const cardHolderNameInputChangeHandler = event => {
		setCardHolderName(event.target.value);
	}

	const cardExpirationDateInputChangeHandler = event => {
		setCardExpirationDate(event.target.value);
	}

	const cardCvvDateInputChangeHandler = event => {
		setCardCvv(event.target.value);
	}

	const cardCardPriceInputChangeHandler = event => {
		setCardPrice(event.target.value);
	}

	useEffect( () => {
		const unsubscribe = onCheckoutValidation( async() => await tokenize(cardNumber, cardHolderName, cardExpirationDate, cardCvv, setCardToken));
		return unsubscribe;
	}, [ onCheckoutValidation, cardNumber, cardHolderName, cardExpirationDate, cardCvv, setCardToken ] );


	useEffect( () => {
		const unsubscribe = onPaymentSetup( () => {
			const paymentMethodData = {
				pagarme: JSON.stringify({
					credit_card: {
						cards: {
							'1': {
								token: cardToken.token,
								brand: 'visa',
								installment: 1
							}
						}
					}
				}),
				payment_method: 'credit_card'
			}

			const response = {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData
				}
			};
			
			return response;
		});
		return unsubscribe;
	}, [ onPaymentSetup, cardToken] );

	useEffect(() => {
		setCardPrice(cartTotal.value);
	}, [cartTotal, setCardPrice])

	return (
		<>
			<div className='wc-block-components-form'>
				{/* <div className='wc-block-components-text-input is-active'>
					<label htmlFor='pagarme[credit_card][cards][1][value]'>Value</label>
					<input type='text' id='pagarme[credit_card][cards][1][value]'
						value={cardPrice}
						onChange={cardCardPriceInputChangeHandler} />
				</div> */}
				<div className='wc-block-components-text-input is-active' >
					<label htmlFor='pagarme[credit_card][cards][1][card-holder-name]'>Card Holder Name</label>
					<input type='text' id='pagarme[credit_card][cards][1][card-holder-name]'
						value={cardHolderName} 
						onChange={cardHolderNameInputChangeHandler} />
				</div>
				<div className='wc-block-components-text-input is-active' >
					<label htmlFor='pagarme[credit_card][cards][1][card-number]'>Card number</label>
					<input type='text' id='pagarme[credit_card][cards][1][card-number]'
							value={cardNumber}
							onChange={cardNumberInputChangeHandler} />
				</div>
				<div className='wc-block-components-text-input is-active' >
					<label htmlFor='pagarme[credit_card][cards][1][card-expiry]'>Expiration Date</label>
					<input type='text' id='pagarme[credit_card][cards][1][card-expiry]'
						value={cardExpirationDate}
						onChange={cardExpirationDateInputChangeHandler} />
				</div>
				<div className='wc-block-components-text-input is-active'>
					<label htmlFor='pagarme[credit_card][cards][1][card-cvv]'>Card code</label>
					<input type='text' id='pagarme[credit_card][cards][1][card-cvv]'
						value={cardCvv}
						onChange={cardCvvDateInputChangeHandler} />
				</div>
				<div className='is-active'>
					<label htmlFor='pagarme[credit_card][cards][1][installment]'>{__('Installments quantity', 'woo-pagarme-payments')}</label>
					<select id='pagarme[credit_card][cards][1][installment]'>
						<option value={"1"}>1x (R$ 35,00)</option>
					</select>
				</div>
				{/* <Button>Abc</Button> */}
				{cardToken && cardToken?.token &&
					<input type='text' id='pagarme[credit_card][cards][1][token]' value={cardToken.token} readOnly />
				}
			</div>
		</>
	);
};

const PagarmeCreditCardLabel = ( props ) => {
    const { PaymentMethodLabel } = props.components;

    return <PaymentMethodLabel text={ 'Cartão de crédito' } />;
}


const pagarmeCreditCardPaymentMethod = {
	name: 'woo-pagarme-payments-credit_card',
	label: <PagarmeCreditCardLabel />,
	content: <PagarmeCrediCardComponent />,
	edit: <PagarmeCrediCardComponent />,
	canMakePayment: () => true,
	ariaLabel: __(
		'Pagar.me Credit Card payment method',
		'woo-pagarme-payments'
	)
};

registerPaymentMethod(pagarmeCreditCardPaymentMethod);