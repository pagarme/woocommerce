import PropTypes from "prop-types";
import useInputHolderName from "./useInputHolderName";

const InputHolderName = ({
    id,
    label,
    inputValue,
    setInputValue,
    cardIndex,
}) => {
    const { setIsActive, cssClasses, inputChangeHandler } = useInputHolderName(
        inputValue,
        setInputValue,
        cardIndex,
    );

    return (
        <div className={cssClasses}>
            <label htmlFor={id}>{label}</label>
            <input
                type="text"
                id={id}
                value={inputValue}
                onChange={inputChangeHandler}
                onFocus={() => setIsActive(true)}
                onBlur={() => {
                    setIsActive(false);
                }}
            />
        </div>
    );
};

InputHolderName.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
};

export default InputHolderName;
