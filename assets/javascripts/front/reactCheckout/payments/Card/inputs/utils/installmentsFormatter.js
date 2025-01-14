/* jshint esversion: 6 */
const { formatPrice } = window.wc.priceFormat;

const formatInstallmentLabel = ({
    optionLabel,
    finalPrice,
    value,
    extraText,
    installmentPrice,
}) => {
    const formatedPrice = formatPrice(installmentPrice);
    const formatedFinalPrice = formatPrice(finalPrice);
    if (value === 1) {
        return `${value}x ${optionLabel} ${formatedPrice}`;
    }

    return `${value}x ${optionLabel} ${formatedPrice} (${formatedFinalPrice}) ${extraText}`.trim();
};

const formatInstallmentsOptions = (installments) => {
    return installments.map((installment) => {
        return {
            label: formatInstallmentLabel(installment),
            value: installment.value,
        };
    });
};

export default formatInstallmentsOptions;
