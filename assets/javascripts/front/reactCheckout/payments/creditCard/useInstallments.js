import { useEffect, useState } from '@wordpress/element';
import formatInstallmentsOptions from './installmentsFormatter';
import usePrevious from '../components/usePrevious';


const useInstallments = (installments, installmentsType, brand, cartTotal, setSelectedInstallment, setIsLoading, cardIndex) => {
    const [installmentsOptions, setInstallmentsOptions] = useState(formatInstallmentsOptions(installments));

    const previousCartTotal = usePrevious(cartTotal);

    useEffect(() => {
        (async () => {
            const canNotUpdateInstallments = (installmentsType === 2 && !brand)
                || (installmentsType === 1
                    && (!previousCartTotal || previousCartTotal === cartTotal));
            if (canNotUpdateInstallments) {
                return;
            }

            setIsLoading(true);
            const formatedCartTotal = parseFloat(cartTotal / 100).toFixed(2).replace('.', ',');

            try {
                const response = await fetch('/wp-admin/admin-ajax.php?'+ new URLSearchParams({
                    action: 'xqRhBHJ5sW',
                    flag: brand,
                    total: formatedCartTotal
                }), {
                    headers: {
                        'X-Request-Type': 'Ajax'
                    }
                });
    
                if (!response.ok) {
                    setIsLoading(false)
                    return;
                }
    
                const result = await response.json();
    
    
                if (!result?.installments?.length) {
                    setIsLoading(false)
                    return;
                }
    
                setInstallmentsOptions(formatInstallmentsOptions(result.installments));
                setSelectedInstallment(cardIndex, 1);
                setIsLoading(false);
            } catch (e) {
                setIsLoading(false)
                return;
            }
        })();

    }, [brand, cartTotal, installmentsType, setInstallmentsOptions, formatInstallmentsOptions, setSelectedInstallment, cardIndex])
    
    const filterHandler = (inputValue) => {
        installmentsOptions.filter( ( option ) =>
            option.label
                .toLowerCase()
                .startsWith( inputValue.toLowerCase() )
        )
    }

    const installmentsChangeHandler = (value) => {
        setSelectedInstallment(cardIndex, value);
    }

    return {
        installmentsOptions,
        filterHandler,
        installmentsChangeHandler
    };
}

export default useInstallments;