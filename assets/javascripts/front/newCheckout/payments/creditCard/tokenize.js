
import { getMonthAndYearFromExpirationDate } from "./utils"


export async function tokenize(cardNumber, cardHolderName, cardExpirationDate, cardCvv, setCardToken) {
    const [month, year] = getMonthAndYearFromExpirationDate(cardExpirationDate);
    const data = {
        card: {
            holder_name: cardHolderName,
            number: cardNumber.replace(/\s/g, ''),
            exp_month: month,
            exp_year: year,
            cvv: cardCvv
        }
    }
    
    try {
        const response = await fetch('https://api.pagar.me/core/v5/tokens?appId=', {
            method: "POST",
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            return {errorMessage: 'teste'};
        }

        const jsonReponse = await response.json();

        setCardToken({token: jsonReponse.id, expiresAt: jsonReponse.expires_at});
        console.log('response:', jsonReponse);

        return true;
    } catch (e) {
        return false;
    }
}