import { useState } from "@wordpress/element";

const useMaskedInput = (inputValue, setInputValue, cardIndex) => {
    const [isActive, setIsActive] = useState(false);

    let cssClasses = "wc-block-components-text-input";

    if (isActive || inputValue.length) {
        cssClasses += " is-active";
    }

    const inputChangeHandler = (event) => {
        setInputValue(cardIndex, event.target.value);
    };

    return {
        setIsActive,
        cssClasses,
        inputChangeHandler,
    };
};

export default useMaskedInput;
