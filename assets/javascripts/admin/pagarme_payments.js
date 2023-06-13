(function ($) {
        const installmentsTypeSelect = $('[data-element="installments-type-select"]');
        const installmentsMax = $('[data-field="installments-maximum"]');
        const installmentsInterest = $('[data-field="installments-interest"]');
        const installmentsMinAmount = $('[data-field="installments-min-amount"]');
        const installmentsByFlag = $('[data-field="installments-by-flag"]');
        const installmentsWithoutInterest = $('[data-field="installments-without-interest"]');
        const installmentsInterestIncrease = $('[data-field="installments-interest-increase"]');
        const flagsSelect = $('[data-element="flags-select"]');
        const installmentsMaxByFlags = $('[data-field="installments-maximum-by-flag"]');

        $.jMaskGlobals.watchDataMask = true;
        handleInstallmentFieldsVisibility(installmentsTypeSelect.val());
        addEventListener();

        function handleInstallmentFieldsVisibility(value) {
            const installmentsMaxContainer = installmentsMax.closest('tr'),
                installmentsInterestContainer = installmentsInterest.closest('tr'),
                installmentsMinAmountContainer = installmentsMinAmount.closest("tr"),
                installmentsByFlagContainer = installmentsByFlag.closest('tr'),
                installmentsWithoutInterestContainer = installmentsWithoutInterest.closest('tr'),
                installmentsInterestIncreaseContainer = installmentsInterestIncrease.closest('tr');

            if (parseInt(value) === 1) {
                installmentsMaxContainer.show();
                installmentsMinAmountContainer.show();
                installmentsInterestContainer.show();
                installmentsInterestIncreaseContainer.show();
                installmentsWithoutInterestContainer.show();
                installmentsByFlagContainer.hide();
            } else {
                if (flagsSelect.val()) {
                    installmentsByFlagContainer.show();
                    setInstallmentsByFlags(null, true);
                }
                installmentsMaxContainer.hide();
                installmentsMinAmountContainer.hide();
                installmentsInterestContainer.hide();
                installmentsInterestIncreaseContainer.hide();
                installmentsWithoutInterestContainer.hide();
            }
        }

        function toggleItemWhenItemFlagIsInFlags(flags, item) {
            if (!flags.includes(item.data('flag'))) {
                item.hide();
            } else {
                item.show();
            }
        }

        function setInstallmentsByFlags(event, firstLoad) {
            const flags = flagsSelect.val() || [];
            const flagsWrapper = installmentsByFlag.closest('tr');
            const allFlags = $('[data-flag]');

            if (parseInt(installmentsTypeSelect.val()) !== 2) {
                allFlags.hide();
                flagsWrapper.hide();
                return;
            }

            if (!firstLoad) {
                const selectedItem = event.params.args.data.id;
                let filtered = flags;

                flagsWrapper.show();

                if (event.params.name === 'unselect') {
                    filtered = flags.filter(function (i) {
                        return i !== selectedItem;
                    });

                    if (filtered.length === 0) {
                        installmentsByFlag.closest('tr').hide();
                    }
                } else {
                    filtered.push(selectedItem);
                }

                allFlags.hide();

                filtered.map(function (item) {
                    const element = $(`[data-flag=${item}]`);
                    element.show();
                });
            } else {
                if (flags.length === 0) {
                    allFlags.hide();
                    flagsWrapper.hide();
                    return;
                }

                allFlags.each(function (index, item) {
                    item = $(item);
                    toggleItemWhenItemFlagIsInFlags(flags, item);
                });
            }
        }

        const setLowestValueToElement = (element, value) => {
            const elementValueGreaterThanNewValue = parseInt(value) < parseInt(element.val());
            if (elementValueGreaterThanNewValue) {
                element.val(value);
            }
        };

        const handleInstallmentWithoutInterestMaxValue = (value) => {
            setLowestValueToElement(installmentsWithoutInterest, value);

            function toggleInstallmentsWithoutInterestOption() {
                const optionValueGreaterThanInstallmentMaximumValue = parseInt($(this).val()) > parseInt(value);
                if (optionValueGreaterThanInstallmentMaximumValue) {
                    $(this).hide();
                    return;
                }

                $(this).show();
            }

            installmentsWithoutInterest.find('option').each(toggleInstallmentsWithoutInterestOption);
        };

        const handleInstallmentsWithoutInterestFlagMaxValue = (element, value) => {
            const installmentsWithoutInterestByFlag = $(element).closest('tr')
                .find('[data-field="installments-without-interest-by-flag"]');

            setLowestValueToElement(installmentsWithoutInterestByFlag, value);

            installmentsWithoutInterestByFlag.attr('max', parseInt(value));
        };

        installmentsMax.each(() => {
            handleInstallmentWithoutInterestMaxValue($(installmentsMax).val());
        });

        installmentsMaxByFlags.each((index, item) => {
            handleInstallmentsWithoutInterestFlagMaxValue(item, $(item).val());
        });

        function addEventListener() {
            installmentsTypeSelect.on('change', function (event) {
                handleInstallmentFieldsVisibility(event.currentTarget.value);
            });
            installmentsMax.on('change', function (event) {
                handleInstallmentWithoutInterestMaxValue(event.currentTarget.value);
            });
            installmentsMaxByFlags.on('change', function (event) {
                handleInstallmentsWithoutInterestFlagMaxValue($(this), event.currentTarget.value)
            });
            flagsSelect.on('select2:unselecting', function (event) {
                setInstallmentsByFlags(event, false);
            });
            flagsSelect.on('select2:selecting', function (event) {
                setInstallmentsByFlags(event, false);
            });
        }

    }(jQuery)
);
