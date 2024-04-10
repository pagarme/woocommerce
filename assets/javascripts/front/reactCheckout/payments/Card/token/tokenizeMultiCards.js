/* jshint esversion: 9 */
import { tokenize } from "./token";
import TokenizeException from "./tokenizeException";

const tokenizeMultiCards = async (cards, cardsNumber, backendConfig) => {
    const dataCards = [];
    for (let cardIndex = 1; cardIndex < cardsNumber + 1; ++cardIndex) {
        const {
            holderName,
            number,
            expirationDate,
            cvv,
            brand,
            installment,
            saveCard,
            walletId,
        } = cards[cardIndex];

        if (walletId.length > 0) {
            dataCards[cardIndex] = {
                "wallet-id": walletId,
                brand: brand,
                installment: installment,
            };

            continue;
        }

        const result = await tokenize(
            number,
            holderName,
            expirationDate,
            cvv,
            backendConfig.appId,
            backendConfig.errorMessages,
        );

        if (result.errorMessage) {
            throw new TokenizeException(result.errorMessage);
        }

        dataCards[cardIndex] = {
            token: result.token,
            brand: brand,
            installment: installment,
        };

        if (saveCard) {
            dataCards[cardIndex]["save-card"] = saveCard;
        }
    }

    return dataCards;
};

export default tokenizeMultiCards;
