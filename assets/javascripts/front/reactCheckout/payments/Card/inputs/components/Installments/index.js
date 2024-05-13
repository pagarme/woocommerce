/* jshint esversion: 8 */
import PropTypes from "prop-types";
import useInstallments from "./useInstallments";

const { ComboboxControl } = wp.components;

const Installments = ({
    label,
    installments,
    installmentsType,
    selectedInstallment,
    setSelectedInstallment,
    brand,
    cartTotal,
    setIsLoading,
    cardIndex,
}) => {
    const { installmentsOptions, filterHandler, installmentsChangeHandler } =
        useInstallments(
            installments,
            installmentsType,
            brand,
            cartTotal,
            setSelectedInstallment,
            setIsLoading,
            cardIndex,
        );

    return (
        <div className="wc-block-components-select-input pagarme-installments-combobox">
            <div className={"wc-block-components-combobox is-active"}>
                <ComboboxControl
                    className={"wc-block-components-combobox-control"}
                    label={label}
                    onChange={installmentsChangeHandler}
                    value={selectedInstallment}
                    options={installmentsOptions}
                    onFilterValueChange={filterHandler}
                    allowReset={false}
                    autoComplete={"off"}
                />
            </div>
        </div>
    );
};

Installments.propTypes = {
    label: PropTypes.string.isRequired,
    installments: PropTypes.array.isRequired,
    installmentsType: PropTypes.number.isRequired,
    selectedInstallment: PropTypes.oneOfType([
        PropTypes.string,
        PropTypes.number,
    ]).isRequired,
    setSelectedInstallment: PropTypes.func.isRequired,
    brand: PropTypes.string.isRequired,
    cartTotal: PropTypes.number.isRequired,
    setIsLoading: PropTypes.func.isRequired,
    cardIndex: PropTypes.number.isRequired,
};

export default Installments;
