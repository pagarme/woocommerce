import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import Installments from './installments';
import InputHolderName from './inputHolderName';
import InputNumber from './inputNumber';
import MaskedInput from './maskedInput';
import PropTypes from 'prop-types';
import pagarmeCardsStore from '../store/cards';
const { CheckboxControl } = window.wc.blocksComponents;

const Card = (props) => {

	const { billing, components, backendConfig, cardIndex } = props;
	const { LoadingMask } = components;


	const [isLoading, setIsLoading ] = useState(false);

	const {
		setHolderName,
		setNumber,
		setExpirationDate,
		setInstallment,
		setBrand,
		setCvv,
		setSaveCard
	} = useDispatch(pagarmeCardsStore);

	const holderName = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getHolderName(cardIndex);
		},
		[cardIndex]
	);

	const number = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getNumber(cardIndex);
		},
		[cardIndex]
	);

	const expirationDate = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getExpirationDate(cardIndex);
		},
		[cardIndex]
	);

	const selectedInstallment = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getInstallment(cardIndex);
		},
		[cardIndex]
	);

	const brand = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getBrand(cardIndex);
		},
		[cardIndex]
	);

	const cvv = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getCvv(cardIndex);
		},
		[cardIndex]
	);

	const saveCard = useSelect(
		(select) => {
			return select(pagarmeCardsStore).getSaveCard(cardIndex);
		},
		[cardIndex]
	);

	const saveCardChangeHandler = (value) => {
		setSaveCard(cardIndex, value);
	}

    const {
        holderNameLabel,
        numberLabel,
        expiryLabel,
        cvvLabel,
        installmentsLabel,
        saveCardLabel,
        walletLabel
    } = backendConfig.fieldsLabels;



	const formatFieldId = (id) => `pagarme_credit_card_${cardIndex}_${id}`;

	return (
		<LoadingMask isLoading={isLoading} >
			<div className='wc-block-components-form'>
				<InputHolderName
					id={formatFieldId('holder_name')}
					label={holderNameLabel}
					inputValue={holderName}
					setInputValue={setHolderName}
					cardIndex={cardIndex}

				/>
				<InputNumber
					id={formatFieldId('number')}
					label={numberLabel}
					inputValue={number}
					setInputValue={setNumber}
					brand={brand}
					setBrand={setBrand}
					brands={backendConfig.brands}
					setIsLoading={setIsLoading}
					cardIndex={cardIndex}
				/>
				<MaskedInput
					id={formatFieldId('expiry')}
					label={expiryLabel}
					mask="99/99"
					inputValue={expirationDate}
					setInputValue={setExpirationDate}
					cardIndex={cardIndex}
				/>
				<MaskedInput
					id={formatFieldId('cvv')}
					label={cvvLabel}
					mask="9999"
					inputValue={cvv}
					setInputValue={setCvv}
					cardIndex={cardIndex}
				/>
				{backendConfig.walletEnabled && (
					<CheckboxControl
						label={saveCardLabel}
						checked={saveCard}
						onChange={saveCardChangeHandler}
					/>
				)}
			</div>
			<Installments
				label={installmentsLabel}
				installments={backendConfig.installments}
				installmentsType={backendConfig.installmentsType}
				selectedInstallment={selectedInstallment}
				setSelectedInstallment={setInstallment}
				brand={brand}
				cartTotal={billing.cartTotal.value}
				setIsLoading={setIsLoading}
				cardIndex={cardIndex}
			/>
		</LoadingMask>
    );
};


export default Card;