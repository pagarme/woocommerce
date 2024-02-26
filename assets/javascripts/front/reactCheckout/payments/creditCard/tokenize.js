import { formatCardNumber, getMonthAndYearFromExpirationDate } from "./utils"


const tranlasteErrorMessage = (errorIndex, message, errorMessages) => {
    const error = errorIndex.replace('request.', '');
    const output = `${error}: ${message}`;
    if (errorMessages.hasOwnProperty(output)) {
        return errorMessages[output];
    }

    return output;
};

const buildErrorMessage = (response, errorMessages) => {
    let errorMessage = '<ul>';
    for (const errorIndex  in response.errors) {
        const message = tranlasteErrorMessage(errorIndex, response.errors[errorIndex], errorMessages);
        errorMessage += `<li>${message}</li>`;
    }
    errorMessage += '</ul>';

    return errorMessage;
}


export async function tokenize(cardNumber, cardHolderName, cardExpirationDate, cardCvv, appId, errorMessages) {
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
            const responseBody = await response.text();
            if (responseBody.length === 0) {
                return { errorMessage: errorMessages.serviceUnavailable };
            }

            const jsonReponse = JSON.parse(responseBody);

            const errorMessage = buildErrorMessage(jsonReponse, errorMessages);

            return { errorMessage };
        }

        const jsonReponse = await response.json();

        return { token: jsonReponse.id };
    } catch (e) {
        return { errorMessage: errorMessages.serviceUnavailable };
    }
}