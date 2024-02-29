import { useState } from "@wordpress/element";

const useInputHolderName = (inputValue, setInputValue, cardIndex) => {
    const [isActive, setIsActive] = useState(false);

    let cssClasses = "wc-block-components-text-input";

    if (isActive || inputValue.length) {
        cssClasses += " is-active";
    }

    const inputChangeHandler = (event) => {
        const result = event.target.value.replace(/[^a-z ]/gi, "");
        setInputValue(cardIndex, result);
    };

    return {
        setIsActive,
        cssClasses,
        inputChangeHandler,
    };
};

export default useInputHolderName;
