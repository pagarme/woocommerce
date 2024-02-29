import InputMask from "react-input-mask";
import PropTypes from "prop-types";
import useMaskedInput from "./useMaskedInput";

const MaskedInput = ({
    id,
    label,
    inputValue,
    setInputValue,
    cardIndex,
    mask,
    maskChar = null,
}) => {
    const { setIsActive, cssClasses, inputChangeHandler } = useMaskedInput(
        inputValue,
        setInputValue,
        cardIndex,
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
                onBlur={() => {
                    setIsActive(false);
                }}
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
};

export default MaskedInput;
