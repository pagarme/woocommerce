/* jshint esversion: 9 */
import useCardValidation from "../../../useCardValidation";
import {useState} from "@wordpress/element";

const useInputHolderName = (inputValue, setInputValue, cardIndex, errors, setErrors, fieldErrors) => {
    const {validateInputHolderName} = useCardValidation(cardIndex, errors, setErrors, fieldErrors);

    let cssClasses = "wc-block-components-text-input";

    const [isActive, setIsActive] = useState(false);
    if (isActive || inputValue.length) {
        cssClasses += " is-active";
    }

    if (errors.hasOwnProperty('inputHolderName')) {
        cssClasses += " has-error";
    }

    const inputChangeHandler = (event) => {
        const result = event.target.value.replace(/[^a-z ]/gi, "");
        setInputValue(cardIndex, result);
    };

    const inputBlurHandler = (event) => {
        validateInputHolderName(event.target.value);
        setIsActive(false);
    };

    return {
        setIsActive,
        cssClasses,
        inputChangeHandler,
        inputBlurHandler,
    };
};

export default useInputHolderName;
