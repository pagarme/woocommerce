import { formatCardNumber, getMonthAndYearFromExpirationDate } from "./utils"


export async function tokenize(cardNumber, cardHolderName, cardExpirationDate, cardCvv, appId) {
    const [month, year] = getMonthAndYearFromExpirationDate(cardExpirationDate);
    const data = {
        card: {
            holder_name: cardHolderName,
            number: formatCardNumber(cardNumber),
            exp_month: month,
            exp_year: year,
            cvv: cardCvv
        }
    }

    try {
        const tokenUrl = `https://api.pagar.me/core/v5/tokens?appId=${appId}`;
        const response = await fetch(tokenUrl, {
            method: "POST",
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            return {errorMessage: 'teste'};
        }

        const jsonReponse = await response.json();

        console.log('response:', jsonReponse);

        return { token: jsonReponse.id };
    } catch (e) {
        return false;
    }
}