/* jshint esversion: 9 */
import Installments from "./inputs/components/Installments";
import InputHolderName from "./inputs/components/InputHolderName";
import InputNumber from "./inputs/components/InputNumber";
import InputExpiry from "./inputs/components/InputExpiry";
import InputCvv from "./inputs/components/InputCvv";
import PropTypes from "prop-types";
import Wallet from "./inputs/components/Wallet";
import useCard from "./useCard";

const {CheckboxControl} = window.wc.blocksComponents;

const Card = ({
    billing,
    components,
    backendConfig,
    cardIndex,
    eventRegistration,
}) => {
    const {LoadingMask} = components;

    const {
        holderNameLabel,
        numberLabel,
        expiryLabel,
        cvvLabel,
        installmentsLabel,
        saveCardLabel,
        walletLabel,
    } = backendConfig.fieldsLabels;

    const {
        isLoading,
        setIsLoading,
        setHolderName,
        setNumber,
        setExpirationDate,
        setInstallment,
        setBrand,
        setCvv,
        setWalletId,
        setErrors,
        saveCardChangeHandler,
        formatFieldId,
        holderName,
        number,
        expirationDate,
        selectedInstallment,
        brand,
        cvv,
        saveCard,
        walletId,
        errors,
    } = useCard(cardIndex, eventRegistration, backendConfig);

    return (
        <LoadingMask isLoading={isLoading}>
            <div className="wc-block-components-form">
                {backendConfig?.walletEnabled && backendConfig?.cards?.length > 0 && (
                    <Wallet
                        label={walletLabel}
                        selectedCard={walletId}
                        cards={backendConfig.cards}
                        cardIndex={cardIndex}
                        setSelectCard={setWalletId}
                        setBrand={setBrand}
                    />
                )}
                {walletId.length === 0 && (
                    <>
                        <InputHolderName
                            id={formatFieldId("holder_name")}
                            label={holderNameLabel}
                            inputValue={holderName}
                            setInputValue={setHolderName}
                            cardIndex={cardIndex}
                            errors={errors}
                            setErrors={setErrors}
                            fieldErrors={backendConfig?.fieldErrors}
                        />
                        <InputNumber
                            id={formatFieldId("number")}
                            label={numberLabel}
                            inputValue={number}
                            setInputValue={setNumber}
                            brand={brand}
                            setBrand={setBrand}
                            brands={backendConfig?.brands}
                            setIsLoading={setIsLoading}
                            cardIndex={cardIndex}
                            errors={errors}
                            setErrors={setErrors}
                            fieldErrors={backendConfig?.fieldErrors}
                        />
                        <InputExpiry
                            id={formatFieldId("expiry")}
                            label={expiryLabel}
                            inputValue={expirationDate}
                            setInputValue={setExpirationDate}
                            cardIndex={cardIndex}
                            errors={errors}
                            setErrors={setErrors}
                            fieldErrors={backendConfig?.fieldErrors}
                        />
                        <InputCvv
                            id={formatFieldId("cvv")}
                            label={cvvLabel}
                            inputValue={cvv}
                            setInputValue={setCvv}
                            cardIndex={cardIndex}
                            errors={errors}
                            setErrors={setErrors}
                            fieldErrors={backendConfig?.fieldErrors}
                        />
                    </>
                )}
                <Installments
                    label={installmentsLabel}
                    installments={backendConfig?.installments}
                    installmentsType={backendConfig?.installmentsType}
                    selectedInstallment={selectedInstallment}
                    setSelectedInstallment={setInstallment}
                    brand={brand}
                    cartTotal={billing.cartTotal.value}
                    setIsLoading={setIsLoading}
                    cardIndex={cardIndex}
                />
                {walletId.length === 0 && backendConfig?.walletEnabled && (
                    <CheckboxControl
                        label={saveCardLabel}
                        checked={saveCard}
                        onChange={saveCardChangeHandler}
                    />
                )}
            </div>
        </LoadingMask>
    );
};

Card.propType = {
    billing: PropTypes.object.isRequired,
    components: PropTypes.object.isRequired,
    backendConfig: PropTypes.object.isRequired,
    cardIndex: PropTypes.number.isRequired,
    eventRegistration: PropTypes.object.isRequired,
};

export default Card;
