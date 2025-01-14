/* jshint esversion: 8 */
import PropTypes from "prop-types";
import { SelectControl } from '@wordpress/components';
import useInstallments from "./useInstallments";

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
        <div className="wc-blocks-components-select">
            <SelectControl
                label={label}
                onChange={installmentsChangeHandler}
                value={selectedInstallment}
                options={installmentsOptions}
                onFilterValueChange={filterHandler}
                allowReset={false}
                autoComplete={"off"}
            />
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
