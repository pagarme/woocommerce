import { useState } from '@wordpress/element';
import InputMask from 'react-input-mask';
import PropTypes from 'prop-types';

const MaskedInput = ({id, label, inputValue, setInputValue, cardIndex, mask, maskChar = null}) => {
    const [isActive, setIsActive] = useState(false);

    let cssClasses = 'wc-block-components-text-input';

    if (isActive || inputValue.length) {
        cssClasses += ' is-active';
    }

    const inputChangeHandler = event => {
        setInputValue(cardIndex, event.target.value)
    }

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
                onFocus={ () => setIsActive( true ) }
                onBlur={ () => {setIsActive( false )} }
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
