/* jshint esversion: 9 */
import { useState } from "@wordpress/element";
import { formatCardNumber } from "../../utils/cardNumberFormatter";
import useCardValidation from "../../../useCardValidation";

const binUrl = "https://api.pagar.me/bin/v1/";
const mundipaggCdn =
    "https://cdn.mundipagg.com/assets/images/logos/brands/png/";

const useInputNumber = (
    inputValue,
    brands,
    setInputValue,
    setBrand,
    setIsLoading,
    cardIndex,
    errors,
    setErrors,
    fieldErrors,
) => {
    const {validateInputNumber} = useCardValidation(cardIndex, errors, setErrors, fieldErrors);

    const [brandImageSrc, setBrandImageSrc] = useState("");

    let cssClasses = "wc-block-components-text-input pagarme-credit-card-number-container";

    const [isActive, setIsActive] = useState(false);
    if (isActive || inputValue.length) {
        cssClasses += " is-active";
    }

    if (errors.hasOwnProperty('inputNumber')) {
        cssClasses += " has-error";
    }

    const resetBrand = () => {
        setBrand(cardIndex, "");
        setBrandImageSrc("");
    };

    const getBrandContingency = (bin) => {
        let oldPrefix = "";
        let brand = null;
        for (const [currentBrandKey, currentBrand] of Object.entries(brands)) {
            for (const prefix of currentBrand.prefixes) {
                const prefixText = prefix.toString();
                if (
                    bin.indexOf(prefixText) === 0 &&
                    oldPrefix.length < prefixText.length
                ) {
                    oldPrefix = prefixText;
                    brand = currentBrandKey;
                }
            }
        }

        return brand;
    };

    const changeBrand = async (cardNumber) => {
        if (cardNumber.length !== 16) {
            resetBrand();
            return;
        }

        setIsLoading(true);
        const bin = cardNumber.substring(0, 6);
        const binFormattedUrl = `${binUrl}${bin}`;

        try {
            const response = await fetch(binFormattedUrl);
            const result = await response.json();

            let brand = result.brand;
            if (!response.ok || typeof result.brandName == "undefined") {
                brand = getBrandContingency(bin);
            }

            if (brand === null) {
                resetBrand();
                setIsLoading(false);
                return;
            }

            if (result.brandImage) {
                setBrandImageSrc(result.brandImage);
                setIsLoading(false);
                setBrand(cardIndex, brand);
                return;
            }

            const brandImage = `${mundipaggCdn}${brand}.png`;
            setBrandImageSrc(brandImage);
            setIsLoading(false);
            setBrand(cardIndex, brand);
        } catch (e) {
            resetBrand();
            setIsLoading(false);
        }
    };

    const inputChangeHandler = (event) => {
        setInputValue(cardIndex, event.target.value);
        changeBrand(formatCardNumber(event.target.value));
    };

    const inputBlurHandler = (event) => {
        validateInputNumber(event.target.value);
        changeBrand(formatCardNumber(event.target.value));
        setIsActive(false);
    };

    return {
        setIsActive,
        cssClasses,
        brandImageSrc,
        inputChangeHandler,
        inputBlurHandler,
    };
};

export default useInputNumber;
