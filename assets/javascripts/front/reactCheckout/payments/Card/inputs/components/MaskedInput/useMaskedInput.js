/* jshint esversion: 8 */
import { useState } from "@wordpress/element";

const useMaskedInput = (inputValue, setInputValue, cardIndex, validate, validateIndex, errors) => {
    let cssClasses = "wc-block-components-text-input";

    const [isActive, setIsActive] = useState(false);
    if (isActive || inputValue.length) {
        cssClasses += " is-active";
    }

    if (errors.hasOwnProperty(validateIndex)) {
        cssClasses += " has-error";
    }

    const inputChangeHandler = (event) => {
        setInputValue(cardIndex, event.target.value);
    };

    const inputBlurHandler = (event) => {
        validate(event.target.value);
        setIsActive(false);
    };

    return {
        setIsActive,
        cssClasses,
        inputChangeHandler,
        inputBlurHandler,
    };
};

export default useMaskedInput;
