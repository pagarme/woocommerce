MONSTER("Pagarme.Components.Settings", function (Model, $, Utils) {
    var errorClass = Utils.addPrefix("field-error");

    Model.fn.start = function () {
        this.init();
    };

    Model.fn.init = function () {
        this.installments = $('[data-field="installments"]');
        this.billet = $('[data-field="billet"]');
        this.installmentsMax = $('[data-field="installments-maximum"]');
        this.installmentsMinAmount = $('[data-field="installments-min-amount"]');
        this.installmentsInterest = $('[data-field="installments-interest"]');
        this.installmentsByFlag = $('[data-field="installments-by-flag"]');
        this.installmentsWithoutInterest = $(
            '[data-field="installments-without-interest"]'
        );
        this.installmentsInterestIncrease = $(
            '[data-field="installments-interest-increase"]'
        );
        this.antifraudSection = $(
            'h3[id*="woo-pagarme-payments_section_antifraud"]'
        );
        this.antifraudEnabled = $('[data-field="antifraud-enabled"]');
        this.antifraudMinValue = $('[data-field="antifraud-min-value"]');
        this.ccBrands = $('[data-field="flags-select"]');
        this.ccAllowSave = $('[data-field="cc-allow-save"]');
        this.billetBank = $('[data-field="billet-bank"]');
        this.softDescriptor = $('[data-field="soft-descriptor"]');
        this.voucherSection = $(
            'h3[id*="woo-pagarme-payments_section_voucher"]'
        );

        this.voucherEnabled = $('#woocommerce_woo-pagarme-payments_enable_voucher');
        this.voucherSoftDescriptor = $(
            '[data-field="voucher-soft-descriptor"]'
        );
        this.VoucherccBrands = $('[data-field="voucher-flags-select"]');
        this.cardWallet = $('[data-field="card-wallet"]');
        this.voucherCardWallet = $('[data-field="voucher-card-wallet"]');

        this.isGatewayIntegrationType = $(
            'input[id*="woo-pagarme-payments_is_gateway_integration_type"]'
        ).prop("checked");
        this.installmentsMaxByFlag = this.installmentsByFlag.find(
            'input[name*="cc_installments_by_flag[max_installment]"]'
        );
        this.installmentsWithoutInterestByFlag = this.installmentsByFlag.find(
            'input[name*="cc_installments_by_flag[no_interest]"]'
        );

        this.handleInstallmentFieldsVisibility(
            this.elements.installmentsTypeSelect.val()
        );
        this.handleGatewayIntegrationFieldsVisibility(
            this.isGatewayIntegrationType
        );
        this.handleBilletBankRequirement();

        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments();
        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag();

        this.setInstallmentsByFlags(null, true);

        this.addEventListener();
    };

    Model.fn.addEventListener = function () {
        this.on("keyup", "soft-descriptor");
        this.on("change", "environment");
        this.on("change", "installments-type");
        this.on("change", "is-gateway-integration-type");
        this.on("change", "enable-billet");
        this.on("change", "enable-multimethods-billet-card");

        this.elements.flagsSelect.on(
            "select2:unselecting",
            this._onChangeFlags.bind(this)
        );
        this.elements.flagsSelect.on(
            "select2:selecting",
            this._onChangeFlags.bind(this)
        );

        $("#mainform").on("submit", this._onSubmitForm.bind(this));
    };

    Model.fn._onKeyupSoftDescriptor = function (event) {
        var isGatewayIntegrationType = $(
            'input[id*="woo-pagarme-payments_is_gateway_integration_type"]'
        ).prop("checked");

        if (!isGatewayIntegrationType &&
            event.currentTarget.value.length > 13
        ) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        if (isGatewayIntegrationType && event.currentTarget.value.length > 22) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        $(event.currentTarget).removeClass(errorClass);
    };

    Model.fn._onSubmitForm = function (event) {
        this.toTop = false;
        this.items = [];

        this.elements.validate.each(this._eachValidate.bind(this));

        return !~this.items.indexOf(true);
    };

    Model.fn._onChangeInstallmentsType = function (event) {
        this.handleInstallmentFieldsVisibility(event.currentTarget.value);
    };

    Model.fn._onChangeIsGatewayIntegrationType = function (event) {
        this.handleGatewayIntegrationFieldsVisibility(
            event.currentTarget.checked
        );
    };

    Model.fn._onChangeEnableBillet = function () {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeEnableMultimethodsBilletCard = function () {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeFlags = function (event) {
        this.setInstallmentsByFlags(event, false);
    };

    Model.fn._eachValidate = function (index, field) {
        var rect;
        var element = $(field),
            empty = element.isEmptyValue(),
            invalidMaxLength = element.val().length > element.prop("maxLength"),
            isFieldInvalid = empty || invalidMaxLength,
            func = isFieldInvalid ? "addClass" : "removeClass";

        if (!element.is(":visible")) {
            return;
        }

        element[func](errorClass);

        this.items[index] = isFieldInvalid;

        if (!isFieldInvalid) {
            return;
        }

        field.placeholder = field.dataset.errorMsg;

        if (!this.toTop) {
            this.toTop = true;
            rect = field.getBoundingClientRect();
            window.scrollTo(0, rect.top + window.scrollY - 32);
        }
    };

    Model.fn.handleInstallmentFieldsVisibility = function (value) {
        var installmentsMaxContainer = this.installmentsMax.closest("tr"),
            installmentsInterestContainer =
                this.installmentsInterest.closest("tr"),
            installmentsMinAmountContainer =
                this.installmentsMinAmount.closest("tr"),
            installmentsByFlagContainer = this.installmentsByFlag.closest("tr"),
            installmentsWithoutInterestContainer =
                this.installmentsWithoutInterest.closest("tr"),
            installmentsInterestIncreaseContainer =
                this.installmentsInterestIncrease.closest("tr");

        if (value == 1) {
            installmentsMaxContainer.show();
            installmentsInterestContainer.show();
            installmentsInterestIncreaseContainer.show();
            installmentsWithoutInterestContainer.show();
            installmentsMinAmountContainer.show();
            installmentsByFlagContainer.hide();
        } else {
            if (this.elements.flagsSelect.val()) {
                installmentsByFlagContainer.show();
                this.setInstallmentsByFlags(null, true);
            }
            installmentsMaxContainer.hide();
            installmentsMinAmountContainer.hide();
            installmentsInterestContainer.hide();
            installmentsInterestIncreaseContainer.hide();
            installmentsWithoutInterestContainer.hide();
        }
    };

    Model.fn.getOnlyGatewayBrands = function () {
        return (
            'option[value="credz"], ' +
            'option[value="sodexoalimentacao"], ' +
            'option[value="sodexocultura"], ' +
            'option[value="sodexogift"], ' +
            'option[value="sodexopremium"], ' +
            'option[value="sodexorefeicao"], ' +
            'option[value="sodexocombustivel"], ' +
            'option[value="vr"], ' +
            'option[value="alelo"], ' +
            'option[value="banese"], ' +
            'option[value="cabal"]'
        );
    };

    Model.fn.getOnlyGatewayInstallments = function () {
        var installments = "";
        var maxInstallmentsLength =
            this.installmentsMax.children("option").length;

        for (let i = 13; i <= maxInstallmentsLength + 1; i++) {
            installments += `option[value="${i}"], `;
        }

        return installments.slice(0, -2);
    };

    Model.fn.setOriginalSelect = function (select) {
        if (select.data("originalHTML") === undefined) {
            select.data("originalHTML", select.html());
        }
    };

    Model.fn.removeOptions = function (select, options) {
        this.setOriginalSelect(select);
        options.remove();
    };

    Model.fn.restoreOptions = function (select) {
        var originalHTML = select.data("originalHTML");
        if (originalHTML !== undefined) {
            select.html(originalHTML);
        }
    };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments =
        function () {
            var installmentsMaxElement = this.installmentsMax;

            installmentsMaxElement.on("change", function () {
                setMaxInstallmentsWithoutInterest($(this).val());
            });

            function setMaxInstallmentsWithoutInterest(installmentsMax) {
                var installmentsWithoutInterest = $(
                    '[data-field="installments-without-interest"]'
                );
                installmentsWithoutInterest.children("option").hide();
                installmentsWithoutInterest
                    .children("option")
                    .filter(function () {
                        return parseInt($(this).val()) <= installmentsMax;
                    })
                    .show();
                installmentsWithoutInterest.val(installmentsMax).change();
            }
        };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag =
        function () {
            var installmentsMaxElement = this.installmentsMaxByFlag;

            installmentsMaxElement.on("change", function () {
                setMaxInstallmentsWithoutInterest(
                    $(this).val(),
                    $(this).closest("tr").attr("data-flag")
                );
            });

            function setMaxInstallmentsWithoutInterest(
                installmentsMax,
                brandName
            ) {
                var setMaxInstallmentsWithoutInterestOnFlag = $(
                    '[data-field="installments-by-flag"]'
                ).find(
                    `input[name*="cc_installments_by_flag[no_interest][${brandName}]"]`
                );
                setMaxInstallmentsWithoutInterestOnFlag.prop(
                    "max",
                    installmentsMax
                );
            }
        };

    Model.fn.setupPSPOptions = function (
        antifraudEnabled,
        antifraudMinValue,
        ccAllowSave,
        billetBank,
        voucherSoftDescriptor,
        VoucherccBrands,
        cardWallet,
        voucherEnabled,
        voucherCardWallet
    ) {
        antifraudEnabled.hide();
        antifraudMinValue.hide();
        ccAllowSave.hide();
        billetBank.hide();
        this.antifraudSection.hide();
        this.voucherSection.hide();
        voucherSoftDescriptor.hide();
        VoucherccBrands.hide();
        cardWallet.hide();
        voucherEnabled.hide();
        voucherCardWallet.hide();

        this.ccAllowSave.prop("checked", false);
        var $optionsToRemove = this.ccBrands.find(this.getOnlyGatewayBrands());
        this.removeOptions(this.ccBrands, $optionsToRemove);

        $("#woo-pagarme-payments_max_length_span").html("13");
        this.softDescriptor.prop("maxlength", 13);

        var $optionsToRemoveInstallments = this.installmentsMax.find(
            this.getOnlyGatewayInstallments()
        );
        var $optionsToRemoveInstallmentsWithoutInterest =
            this.installmentsWithoutInterest.find(
                this.getOnlyGatewayInstallments()
            );
        this.removeOptions(this.installmentsMax, $optionsToRemoveInstallments);
        this.removeOptions(
            this.installmentsWithoutInterest,
            $optionsToRemoveInstallmentsWithoutInterest
        );

        this.installmentsMaxByFlag.prop("max", 12);
    };

    Model.fn.setupGatewayOptions = function (
        antifraudEnabled,
        antifraudMinValue,
        ccAllowSave,
        billetBank,
        voucherSoftDescriptor,
        VoucherccBrands,
        cardWallet,
        voucherEnabled,
        voucherCardWallet
    ) {
        antifraudEnabled.show();
        antifraudMinValue.show();
        ccAllowSave.show();
        billetBank.show();
        this.antifraudSection.show();
        this.voucherSection.show();
        voucherSoftDescriptor.show();
        VoucherccBrands.show();
        cardWallet.show();
        voucherEnabled.show();
        voucherCardWallet.show();

        this.restoreOptions(this.ccBrands);

        $("#woo-pagarme-payments_max_length_span").html("22");
        this.softDescriptor.prop("maxlength", 22);

        this.restoreOptions(this.installmentsMax);
        this.restoreOptions(this.installmentsWithoutInterest);

        this.installmentsMaxByFlag.prop("max", 24);
    };

    Model.fn.handleGatewayIntegrationFieldsVisibility = function (isGateway) {
        var antifraudEnabled = this.antifraudEnabled.closest("tr"),
            antifraudMinValue = this.antifraudMinValue.closest("tr"),
            ccAllowSave = this.ccAllowSave.closest("tr"),
            billetBank = this.billetBank.closest("tr"),
            voucherSoftDescriptor = this.voucherSoftDescriptor.closest("tr"),
            VoucherccBrands = this.VoucherccBrands.closest("tr"),
            voucherEnabled = this.voucherEnabled.closest('tr'),
            voucherCardWallet = this.voucherCardWallet.closest('tr'),
            cardWallet = this.cardWallet.closest("tr");

        if (isGateway) {
            return this.setupGatewayOptions(
                antifraudEnabled,
                antifraudMinValue,
                ccAllowSave,
                billetBank,
                voucherSoftDescriptor,
                VoucherccBrands,
                cardWallet,
                voucherEnabled,
                voucherCardWallet
            );
        }

        return this.setupPSPOptions(
            antifraudEnabled,
            antifraudMinValue,
            ccAllowSave,
            billetBank,
            voucherSoftDescriptor,
            VoucherccBrands,
            cardWallet,
            voucherEnabled,
            voucherCardWallet
        );
    };

    Model.fn.handleBilletBankRequirement = function () {
        const billetBankElementId =
            "#woocommerce_woo-pagarme-payments_billet_bank";
        let bankRequirementFields = $('[data-requires-field="billet-bank"]');
        let billetBankIsRequired = false;

        bankRequirementFields.each(function () {
            if ($(this).prop("checked")) {
                billetBankIsRequired = true;
                return false;
            }
        });

        if (billetBankIsRequired) {
            $(billetBankElementId).attr("required", true);
            return;
        }

        $(billetBankElementId).attr("required", false);
    };

    Model.fn.setInstallmentsByFlags = function (event, firstLoad) {
        var flags = this.elements.flagsSelect.val() || [];
        var flagsWrapper = this.installmentsByFlag.closest("tr");
        var allFlags = $("[data-flag]");

        if (parseInt(this.elements.installmentsTypeSelect.val()) !== 2) {
            allFlags.hide();
            flagsWrapper.hide();
            return;
        }

        if (!firstLoad) {
            var selectedItem = event.params.args.data.id;
            var filtered = flags;

            flagsWrapper.show();

            if (event.params.name == "unselect") {
                filtered = flags.filter(function (i) {
                    return i != selectedItem;
                });

                if (filtered.length == 0) {
                    this.installmentsByFlag.closest("tr").hide();
                }
            } else {
                filtered.push(selectedItem);
            }

            allFlags.hide();

            filtered.map(function (item) {
                var element = $("[data-flag=" + item + "]");
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
                if (!flags.includes(item.data("flag"))) {
                    item.hide();
                } else {
                    item.show();
                }
            });
        }
    };
});
