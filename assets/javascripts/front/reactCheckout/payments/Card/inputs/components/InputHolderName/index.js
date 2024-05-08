/* jshint esversion: 8 */
import PropTypes from "prop-types";
import useInputHolderName from "./useInputHolderName";

const InputHolderName = ({
    id,
    label,
    inputValue,
    setInputValue,
    cardIndex,
    errors,
    setErrors,
    fieldErrors,
}) => {
    const { setIsActive, cssClasses, inputChangeHandler, inputBlurHandler } = useInputHolderName(
        inputValue,
        setInputValue,
        cardIndex,
        errors,
        setErrors,
        fieldErrors,
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
                onBlur={inputBlurHandler}
            />
            {errors.inputHolderName && (
                <div className="wc-block-components-validation-error" role="alert">
                    <p>{errors.inputHolderName}</p>
                </div>
            )}
        </div>
    );
};

InputHolderName.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
    errors: PropTypes.object.isRequired,
    setErrors: PropTypes.func.isRequired,
    fieldErrors: PropTypes.object.isRequired,
};

export default InputHolderName;
