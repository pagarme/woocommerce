/* jshint esversion: 9 */
import { __ } from '@wordpress/i18n';

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
        const splitDate = value.split('/');
        const expiryMonth = splitDate[0].replace(/(\D)/g, '');
        const validMonth = expiryMonth >= 1 && expiryMonth <= 12;
        if (!validMonth) {
            errors.inputExpiry = fieldErrors.invalidExpiryMonth;
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
