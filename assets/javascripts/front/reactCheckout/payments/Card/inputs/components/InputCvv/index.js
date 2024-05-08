/* jshint esversion: 9 */
import PropTypes from "prop-types";
import MaskedInput from "../MaskedInput";
import useCardValidation from "../../../useCardValidation";

const InputCvv = ({
    id,
    label,
    cardIndex,
    inputValue,
    setInputValue,
    errors,
    setErrors,
    fieldErrors,
}) => {
    const {validateInputCvv} = useCardValidation(cardIndex, errors, setErrors, fieldErrors);

    return (
        <>
            <MaskedInput
                id={id}
                label={label}
                mask="9999"
                inputValue={inputValue}
                setInputValue={setInputValue}
                cardIndex={cardIndex}
                validate={validateInputCvv}
                validateIndex='inputCvv'
                errors={errors}
            />
            {errors.inputCvv && (
                <div className="wc-block-components-validation-error" role="alert">
                    <p>{errors.inputCvv}</p>
                </div>
            )}
        </>
    );
};

InputCvv.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    cardIndex: PropTypes.number.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    errors: PropTypes.object.isRequired,
    setErrors: PropTypes.func.isRequired,
    fieldErrors: PropTypes.object.isRequired,
};

export default InputCvv;
