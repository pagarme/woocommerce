/* jshint esversion: 8 */
import InputMask from "react-input-mask";
import PropTypes from "prop-types";
import useMaskedInput from "./useMaskedInput";

const MaskedInput = ({
    id,
    label,
    inputValue,
    setInputValue,
    cardIndex,
    validate,
    validateIndex,
    mask,
    maskChar = null,
    errors,
}) => {
    const { setIsActive, cssClasses, inputChangeHandler, inputBlurHandler } = useMaskedInput(
        inputValue,
        setInputValue,
        cardIndex,
        validate,
        validateIndex,
        errors,
    );

    return (
        <div className={cssClasses}>
            <label htmlFor={id}>{label}</label>
            <InputMask
                type="text"
                id={id}
                mask={mask}
                maskChar={maskChar}
                onChange={inputChangeHandler}
                value={inputValue}
                onFocus={() => setIsActive(true)}
                onBlur={inputBlurHandler}
            />
        </div>
    );
};

MaskedInput.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
    mask: PropTypes.string.isRequired,
    maskChar: PropTypes.string,
    validate: PropTypes.func.isRequired,
    validateIndex: PropTypes.string.isRequired,
    errors: PropTypes.object.isRequired,
};

export default MaskedInput;
