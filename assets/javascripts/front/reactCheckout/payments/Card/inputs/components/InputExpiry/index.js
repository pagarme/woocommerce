/* jshint esversion: 9 */
import PropTypes from "prop-types";
import MaskedInput from "../MaskedInput";
import useCardValidation from "../../../useCardValidation";

const InputExpiry = ({
    id,
    label,
    cardIndex,
    inputValue,
    setInputValue,
    errors,
    setErrors,
    fieldErrors,
}) => {
    const {validateInputExpiry} = useCardValidation(cardIndex, errors, setErrors, fieldErrors);

    return (
        <>
            <MaskedInput
                id={id}
                label={label}
                mask="99/99"
                maskChar="_"
                inputValue={inputValue}
                setInputValue={setInputValue}
                cardIndex={cardIndex}
                validate={validateInputExpiry}
                validateIndex='inputExpiry'
                errors={errors}
            />
            {errors.inputExpiry && (
                <div className="wc-block-components-validation-error" role="alert">
                    <p>{errors.inputExpiry}</p>
                </div>
            )}
        </>
    );
};

InputExpiry.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    cardIndex: PropTypes.number.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    errors: PropTypes.object.isRequired,
    setErrors: PropTypes.func.isRequired,
    fieldErrors: PropTypes.object.isRequired,
};

export default InputExpiry;
