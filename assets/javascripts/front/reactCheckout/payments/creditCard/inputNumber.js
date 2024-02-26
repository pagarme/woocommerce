import PropTypes from 'prop-types';
import InputMask from 'react-input-mask';
import useInputNumber from './useInputNumber';


const InputNumber = ({id, label, inputValue, setInputValue, brand, setBrand, brands, setIsLoading, cardIndex}) => {

    const { brandImageSrc, inputChangeHandler, changeBrand } = useInputNumber(inputValue, brands, setInputValue, setBrand, setIsLoading, cardIndex);

    return (
        <div className={'wc-block-components-text-input is-active'} >
            <label htmlFor={id}>{label}</label>
            <InputMask
                className={'pagarme-card-form-card-number'}
                type="text"
                id={id}
                mask="9999 9999 9999 9999"
                maskChar="*"
                onChange={inputChangeHandler}
                value={inputValue}
                alwaysShowMask={true}
                onBlur={changeBrand}
            />
            {brandImageSrc && (
                <img src={brandImageSrc} alt={brand} />
            )}
        </div>
    );
}

InputNumber.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    brand: PropTypes.string.isRequired,
    setBrand: PropTypes.func.isRequired,
    brands: PropTypes.array.isRequired,
    setIsLoading: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
};

export default InputNumber;