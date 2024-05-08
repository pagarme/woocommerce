/* jshint esversion: 9 */
import {getMonthAndYearFromExpirationDate} from "./inputs/utils/expirationDate";

const useCardValidation = (cardIndex, errors, setErrors, fieldErrors) => {

    const validateHolderName = (value, errors) => {
        delete errors['inputHolderName'];
        const valid = value.length > 0;
        if (!valid) {
            errors.inputHolderName = fieldErrors.holderName;
        }
        return errors;
    };

    const validateInputHolderName = (value) => {
        const updatedErrors = validateHolderName(value, errors);
        setErrors(cardIndex, {
            ...updatedErrors
        });
        return !!updatedErrors.inputHolderName;
    };

    const validateNumber = (value, errors) => {
        delete errors['inputNumber'];
        value = value.replace(/(\D)/g, '');
        const valid = value.length === 16;
        if (!valid) {
            errors.inputNumber = fieldErrors.cardNumber;
        }
        return errors;
    };

    const validateInputNumber = (value) => {
        const updatedErrors = validateNumber(value, errors);
        setErrors(cardIndex, {
            ...updatedErrors
        });
        return !!updatedErrors.inputNumber;
    };

    const validateExpiry = (value, errors) => {
        delete errors['inputExpiry'];
        const empty = value.length === 0;
        if (empty) {
            errors.inputExpiry = fieldErrors.emptyExpiry;
            return errors;
        }
        const [expiryMonth, expiryYear] = getMonthAndYearFromExpirationDate(value);
        const cardDate = new Date(`20${expiryYear}`, expiryMonth -1);
        let dateNow = new Date();
        dateNow = new Date(dateNow.getFullYear(), dateNow.getMonth());
        const validMonth = expiryMonth >= 1 && expiryMonth <= 12;
        if (!validMonth) {
            errors.inputExpiry = fieldErrors.invalidExpiryMonth;
        }
        const validYear = !(expiryYear.includes('_'));
        if (!validYear) {
            errors.inputExpiry = fieldErrors.invalidExpiryYear;
        }
        if (cardDate < dateNow) {
            errors.inputExpiry = fieldErrors.expiredCard;
        }
        return errors;
    };

    const validateInputExpiry = (value) => {
        const updatedErrors = validateExpiry(value, errors);
        setErrors(cardIndex, {
            ...updatedErrors
        });
        return !!updatedErrors.inputExpiry;
    };

    const validateCvv = (value, errors) => {
        delete errors['inputCvv'];
        value = value.replace(/(\D)/g, '');
        const empty = value.length === 0;
        if (empty) {
            errors.inputCvv = fieldErrors.emptyCvv;
            return errors;
        }
        const valid = value.length === 3 || value.length === 4;
        if (!valid) {
            errors.inputCvv = fieldErrors.invalidCvv;
        }
        return errors;
    };

    const validateInputCvv = (value) => {
        const updatedErrors = validateCvv(value, errors);
        setErrors(cardIndex, {
            ...updatedErrors
        });
        return !!updatedErrors.inputCvv;
    };
    const validateAllFields = (holderName, number, expiry, cvv) => {
        let updatedErrors = {...errors};
        updatedErrors = validateHolderName(holderName, updatedErrors);
        updatedErrors = validateNumber(number, updatedErrors);
        updatedErrors = validateExpiry(expiry, updatedErrors);
        updatedErrors = validateCvv(cvv, updatedErrors);

        setErrors(cardIndex, {
            ...updatedErrors
        });

        return Object.keys(updatedErrors).length === 0;
    };

    return {
        validateInputHolderName,
        validateInputNumber,
        validateInputExpiry,
        validateInputCvv,
        validateAllFields,
    };
};

export default useCardValidation;
