/* jshint esversion: 6 */
(function ($) {
        const
            installmentsTypeSelect = $('[data-element="installments-type-select"]'),
            installmentsMax = $('[data-field="installments-maximum"]'),
            installmentsInterest = $('[data-field="installments-interest"]'),
            installmentsInterestLegacy = $('[data-field="installments-interest-legacy"]'),
            installmentsMinAmount = $('[data-field="installments-min-amount"]'),
            installmentsByFlag = $('[data-field="installments-by-flag"]'),
            installmentsWithoutInterest = $('[data-field="installments-without-interest"]'),
            installmentsInterestIncrease = $('[data-field="installments-interest-increase"]'),
            flagsSelect = $('[data-element="flags-select"]'),
            installmentsMaxByFlags = $('[data-field="installments-maximum-by-flag"]'),

            INSTALLMENTS_LEGACY = '3';

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

        function handleInstallmentFieldsVisibility(value) {
            const installmentsMaxContainer = installmentsMax.closest('tr'),
                installmentsInterestContainer = installmentsInterest.closest('tr'),
                installmentsInterestLegacyContainer = installmentsInterestLegacy.closest('tr'),
                installmentsMinAmountContainer = installmentsMinAmount.closest("tr"),
                installmentsByFlagContainer = installmentsByFlag.closest('tr'),
                installmentsWithoutInterestContainer = installmentsWithoutInterest.closest('tr'),
                installmentsInterestIncreaseContainer = installmentsInterestIncrease.closest('tr');

            switch (parseInt(value)) {
                case 1:
                    installmentsMaxContainer.show();
                    installmentsMinAmountContainer.show();
                    installmentsInterestContainer.show();
                    installmentsInterestIncreaseContainer.show();
                    installmentsInterestLegacyContainer.hide();
                    installmentsWithoutInterestContainer.show();
                    installmentsByFlagContainer.hide();
                    break;
                case 2:
                    if (flagsSelect.val()) {
                        installmentsByFlagContainer.show();
                        setInstallmentsByFlags(null, true);
                    }
                    installmentsMaxContainer.hide();
                    installmentsMinAmountContainer.hide();
                    installmentsInterestContainer.hide();
                    installmentsInterestIncreaseContainer.hide();
                    installmentsInterestLegacyContainer.hide();
                    installmentsWithoutInterestContainer.hide();
                    break;
                case 3:
                    installmentsMaxContainer.show();
                    installmentsMinAmountContainer.show();
                    installmentsInterestContainer.hide();
                    installmentsInterestIncreaseContainer.hide();
                    installmentsInterestLegacyContainer.show();
                    installmentsWithoutInterestContainer.show();
                    installmentsByFlagContainer.hide();
                    break;
                default:
                    installmentsMaxContainer.hide();
                    installmentsMinAmountContainer.hide();
                    installmentsInterestContainer.hide();
                    installmentsInterestIncreaseContainer.hide();
                    installmentsInterestLegacyContainer.hide();
                    installmentsWithoutInterestContainer.hide();
                    installmentsByFlagContainer.hide();
                    break;
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

        const fillLegacyInstallmentInterests = () => {
            if (installmentsTypeSelect.find(":selected").val() !== INSTALLMENTS_LEGACY) {
                return;
            }

            let legacyInterest = installmentsInterestLegacy.val();

            if (legacyInterest === '') {
                installmentsInterest.val('');
                installmentsInterestIncrease.val('');
                return;
            }

            legacyInterest = parseInt(legacyInterest);
            const noInterest = parseInt(installmentsWithoutInterest.val());

            installmentsInterest.val(legacyInterest * (noInterest + 1));
            installmentsInterestIncrease.val(legacyInterest);
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
                handleInstallmentsWithoutInterestFlagMaxValue($(this), event.currentTarget.value);
            });
            flagsSelect.on('select2:unselecting', function (event) {
                setInstallmentsByFlags(event, false);
            });
            flagsSelect.on('select2:selecting', function (event) {
                setInstallmentsByFlags(event, false);
            });
            installmentsTypeSelect.on('change', function() {
                fillLegacyInstallmentInterests();
            });
            installmentsInterestLegacy.on('change', function() {
                fillLegacyInstallmentInterests();
            });
            installmentsWithoutInterest.on('change', function() {
                fillLegacyInstallmentInterests();
            });
        }

        $.jMaskGlobals.watchDataMask = true;
        handleInstallmentFieldsVisibility(installmentsTypeSelect.val());
        addEventListener();

    }(jQuery)
);
