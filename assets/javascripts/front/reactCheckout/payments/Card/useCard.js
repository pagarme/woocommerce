/* jshint esversion: 8 */
import { useDispatch, useSelect } from "@wordpress/data";
import { useState } from "@wordpress/element";
import pagarmeCardsStore from "../store/cards";

const useCard = (cardIndex) => {
    const [isLoading, setIsLoading] = useState(false);

    const {
        setHolderName,
        setNumber,
        setExpirationDate,
        setInstallment,
        setBrand,
        setCvv,
        setSaveCard,
        setWalletId,
        setErrors,
    } = useDispatch(pagarmeCardsStore);

    const holderName = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getHolderName(cardIndex);
        },
        [cardIndex],
    );

    const number = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getNumber(cardIndex);
        },
        [cardIndex],
    );

    const expirationDate = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getExpirationDate(cardIndex);
        },
        [cardIndex],
    );

    const selectedInstallment = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getInstallment(cardIndex);
        },
        [cardIndex],
    );

    const brand = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getBrand(cardIndex);
        },
        [cardIndex],
    );

    const cvv = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getCvv(cardIndex);
        },
        [cardIndex],
    );

    const saveCard = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getSaveCard(cardIndex);
        },
        [cardIndex],
    );

    const walletId = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getWalletId(cardIndex);
        },
        [cardIndex],
    );

    const errors = useSelect(
        (select) => {
            return select(pagarmeCardsStore).getErrors(cardIndex);
        },
        [cardIndex],
    );

    const saveCardChangeHandler = (value) => {
        setSaveCard(cardIndex, value);
    };

    const formatFieldId = (id) => `pagarme_credit_card_${cardIndex}_${id}`;

    return {
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
    };
};

export default useCard;
