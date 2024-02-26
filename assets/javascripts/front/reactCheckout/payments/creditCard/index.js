const { registerPaymentMethod } = window.wc.wcBlocksRegistry;

const { CheckboxControl } = window.wc.blocksComponents;
import { useEffect, useState } from '@wordpress/element';
import Installments from './installments';
import InputHolderName from './inputHolderName';
import InputNumber from './inputNumber';
import MaskedInput from './maskedInput';
import PropTypes from 'prop-types';
// import store from './usePaymentWithCard';
import pagarmeCardsStore from '../store/cards';

import Card from './card';
import { useDispatch, useSelect } from '@wordpress/data';
import { tokenize } from './tokenize';


const backendConfig = wc.wcSettings.getSetting('woo-pagarme-payments-credit_card_data');

const cardIndex = 1;

const PagarmeCreditCardComponent = (props) => {
	
	const { reset } = useDispatch(pagarmeCardsStore);

	const { emitResponse, eventRegistration } = props;

	const { onPaymentSetup } = eventRegistration;

	const cards = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getCards();
		}
	);

	useEffect(() => {
		reset();
	}, []);

	useEffect(() => {
		const unsubscribe = onPaymentSetup(async () => {

			
			const { holderName, number, expirationDate, cvv, brand, installment } = cards[cardIndex];

			const result = await tokenize(number, holderName, expirationDate, cvv, backendConfig.appId, backendConfig.errorMessages);

			if (result.errorMessage) {
				return {
					type: emitResponse.responseTypes.ERROR,
					message: result.errorMessage
				};
			}

			return  {
				type: emitResponse.responseTypes.SUCCESS,
				meta: {
					paymentMethodData: {
						pagarme: JSON.stringify({
							credit_card: {
								cards: {
									[cardIndex]: {
										token: result.token,
										brand: brand,
										installment: installment
									}
								}
							}
						}),
						payment_method: 'credit_card'
					}
				}
			};
		});

		return unsubscribe;
	}, [onPaymentSetup, cards, cardIndex, backendConfig])

	return (
		<Card
			{...props}
			backendConfig={backendConfig}
			cardIndex={cardIndex}
		/>
	);
};

const PagarmeCreditCardLabel = ( { components } ) => {
	const { PaymentMethodLabel } = components;

    return <PaymentMethodLabel text={ backendConfig.label } />;
}

PagarmeCreditCardLabel.propTypes = {
	components: PropTypes.object.isRequired
};


const pagarmeCreditCardPaymentMethod = {
	name: backendConfig.name,
	label: <PagarmeCreditCardLabel />,
	content: <PagarmeCreditCardComponent />,
	edit: <PagarmeCreditCardComponent />,
	canMakePayment: () => true,
	ariaLabel: backendConfig.ariaLabel
};

registerPaymentMethod(pagarmeCreditCardPaymentMethod);