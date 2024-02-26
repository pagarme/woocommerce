import { useState } from '@wordpress/element';
import { formatCardNumber } from './utils';

const binUrl = 'https://api.pagar.me/bin/v1/';
const mundipaggCdn = 'https://cdn.mundipagg.com/assets/images/logos/brands/png/';

const useInputNumber = (inputValue, brands, setInputValue, setBrand, setIsLoading, cardIndex) => {
    const [brandImageSrc, setBrandImageSrc] = useState('');

    const inputChangeHandler = event => {
        setInputValue(cardIndex, event.target.value)
    };

    const getBrandContengency = (bin) => {
        let oldPrefix = '';
        let brand = null;
        for (const [currentBrandKey, currentBrand] of Object.entries(brands)) {
            for (const prefix of currentBrand.prefixes) {
                const prefixText = prefix.toString();
                if (bin.indexOf(prefixText) === 0 && oldPrefix.length < prefixText.length) {
                    oldPrefix = prefixText;
                    brand = currentBrandKey;
                }
            }
        }

        return brand;
    }

    const resetBrand = () => {
        setBrand(cardIndex, '');
        setBrandImageSrc('');
    }

    const changeBrand = async () => {
        const cardNumber = formatCardNumber(inputValue);
        if (cardNumber.length !== 16 ) {
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
            if (!response.ok || typeof result.brandName == 'undefined') {
                brand = getBrandContengency(bin)
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
        } catch(e) {
            resetBrand();
            setIsLoading(false);
            return;
        }
    }

    return {
        brandImageSrc,
        inputChangeHandler,
        changeBrand
    };

};

export default useInputNumber;