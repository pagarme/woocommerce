import Installments from "./inputs/components/Installments";
import InputHolderName from "./inputs/components/InputHolderName";
import InputNumber from "./inputs/components/InputNumber";
import MaskedInput from "./inputs/components/MaskedInput";
import PropTypes from "prop-types";
import Wallet from "./inputs/components/Wallet";
import useCard from "./useCard";
const { CheckboxControl } = window.wc.blocksComponents;

const Card = ({ billing, components, backendConfig, cardIndex }) => {
    const { LoadingMask } = components;

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
    } = useCard(cardIndex);

    return (
        <LoadingMask isLoading={isLoading}>
            <div className="wc-block-components-form">
                {backendConfig.walletEnabled && (
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
                        />
                        <InputNumber
                            id={formatFieldId("number")}
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
                            id={formatFieldId("expiry")}
                            label={expiryLabel}
                            mask="99/99"
                            inputValue={expirationDate}
                            setInputValue={setExpirationDate}
                            cardIndex={cardIndex}
                        />
                        <MaskedInput
                            id={formatFieldId("cvv")}
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
                    </>
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

Card.propType = {
    billing: PropTypes.object.isRequired,
    components: PropTypes.object.isRequired,
    backendConfig: PropTypes.object.isRequired,
    cardIndex: PropTypes.number.isRequired,
};

export default Card;
