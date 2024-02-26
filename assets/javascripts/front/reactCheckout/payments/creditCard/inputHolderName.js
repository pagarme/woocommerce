import { useState } from '@wordpress/element';
import PropTypes from 'prop-types';


const InputHolderName = ({id, label, inputValue, setInputValue, cardIndex}) => {
    const [isActive, setIsActive] = useState(false);

    let cssClasses = 'wc-block-components-text-input';

    if (isActive || inputValue.length) {
        cssClasses += ' is-active';
    }

    const inputChangeHandler = event => {
        const result = event.target.value.replace(/[^a-z ]/gi, '');
        setInputValue(cardIndex, result);
    };

    return (
        <div className={cssClasses} >
            <label htmlFor={id}>{label}</label>
            <input
                type='text'
                id={id}
                value={inputValue} 
                onChange={inputChangeHandler}
                onFocus={ () => setIsActive( true ) }
                onBlur={ () => {setIsActive( false )} }
            />
        </div>
    )
}

InputHolderName.propTypes = {
    id: PropTypes.string.isRequired,
    label: PropTypes.string.isRequired,
    inputValue: PropTypes.string.isRequired,
    setInputValue: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
};

export default InputHolderName;