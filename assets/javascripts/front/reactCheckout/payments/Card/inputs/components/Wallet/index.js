/* jshint esversion: 8 */
import PropTypes from "prop-types";
import useWallet from "./useWallet";
const { ComboboxControl } = wp.components;

const Wallet = ({
    cards,
    label,
    cardIndex,
    selectedCard,
    setSelectCard,
    setBrand,
}) => {
    const { filterHandler, cardChangeHandler } = useWallet(
        cards,
        cardIndex,
        setSelectCard,
        setBrand,
    );

    return (
        <div className="wc-block-components-select-input pagarme-installments-combobox">
            <div className={"wc-block-components-combobox is-active"}>
                <ComboboxControl
                    className={"wc-block-components-combobox-control"}
                    label={label}
                    onChange={cardChangeHandler}
                    value={selectedCard}
                    options={cards}
                    onFilterValueChange={filterHandler}
                    allowReset={false}
                    autoComplete={"off"}
                />
            </div>
        </div>
    );
};

Wallet.propTypes = {
    cards: PropTypes.array.isRequired,
    label: PropTypes.string.isRequired,
    cardIndex: PropTypes.number.isRequired,
    selectedCard: PropTypes.string.isRequired,
    setSelectCard: PropTypes.func.isRequired,
    setBrand: PropTypes.func.isRequired,
};

export default Wallet;
