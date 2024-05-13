import { formatCardNumber } from "../inputs/utils/cardNumberFormatter";
import { getMonthAndYearFromExpirationDate } from "../inputs/utils/expirationDate";

const tranlasteErrorMessage = (errorIndex, message, errorMessages) => {
    const error = errorIndex.replace("request.", "").replace("card.", "");
    const output = `${error}: ${message}`;
    if (errorMessages.hasOwnProperty(output)) {
        return errorMessages[output];
    }

    return "";
};

const buildErrorMessage = (response, errorMessages) => {
    let errorMessage = "";
    for (const errorIndex in response.errors) {
        for (const error of response.errors[errorIndex] || []) {
            const message = tranlasteErrorMessage(
                errorIndex,
                error,
                errorMessages,
            );
            if (message.length === 0) {
                continue;
            }
            errorMessage += `${message}<br/>`;
        }
    }

    return errorMessage;
};

export async function tokenize(
    cardNumber,
    cardHolderName,
    cardExpirationDate,
    cardCvv,
    appId,
    errorMessages,
) {
    const [month, year] = getMonthAndYearFromExpirationDate(cardExpirationDate);
    const data = {
        card: {
            holder_name: cardHolderName,
            number: formatCardNumber(cardNumber),
            exp_month: month,
            exp_year: year,
            cvv: cardCvv,
        },
    };

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

            const jsonErrorResponse = JSON.parse(responseBody);

            const errorMessage = buildErrorMessage(jsonErrorResponse, errorMessages);

            return { errorMessage };
        }

        const jsonResponse = await response.json();

        return { token: jsonResponse.id };
    } catch (e) {
        return { errorMessage: errorMessages.serviceUnavailable };
    }
}
