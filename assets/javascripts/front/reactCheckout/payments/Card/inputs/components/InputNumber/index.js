/* jshint esversion: 8 */
import PropTypes from "prop-types";
import InputMask from "react-input-mask";
import useInputNumber from "./useInputNumber";

const InputNumber = ({
    id,
    label,
    inputValue,
    setInputValue,
    brand,
    setBrand,
    brands,
    apiUrls,
    setIsLoading,
    cardIndex,
    errors,
    setErrors,
    fieldErrors,
}) => {
    const { setIsActive, cssClasses, brandImageSrc, inputChangeHandler, inputBlurHandler} = useInputNumber(
        inputValue,
        brands,
        setInputValue,
        setBrand,
        setIsLoading,
        cardIndex,
        errors,
        setErrors,
        fieldErrors,
        apiUrls,
    );

    return (
        <div className={cssClasses}>
            <label htmlFor={id}>{label}</label>
            <InputMask
                className={"pagarme-card-form-card-number"}
                type="text"
                id={id}
                mask="9999 9999 9999 9999"
                maskChar="•"
                onFocus={() => setIsActive(true)}
                onChange={inputChangeHandler}
                value={inputValue}
                onBlur={inputBlurHandler}
            />
            {brandImageSrc && <img src={brandImageSrc} alt={brand}/>}
            {errors.inputNumber && (
                <div className="wc-block-components-validation-error" role="alert">
                    <p>{errors.inputNumber}</p>
                </div>
            )}
        </div>
    );
};

InputNumber.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    brand: PropTypes.string.isRequired,
    setBrand: PropTypes.func.isRequired,
    brands: PropTypes.object.isRequired,
    apiUrls: PropTypes.shape({
        bin: PropTypes.string.isRequired,
        cardBrandsCdn: PropTypes.string.isRequired,
    }).isRequired,
    setIsLoading: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
    errors: PropTypes.object.isRequired,
    setErrors: PropTypes.func.isRequired,
    fieldErrors: PropTypes.object.isRequired,
};

export default InputNumber;
