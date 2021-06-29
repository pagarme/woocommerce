MONSTER( 'Pagarme.Components.Settings', function(Model, $, Utils) {

    var errorClass = Utils.addPrefix( 'field-error' );

    Model.fn.start = function() {
        this.init();
    };

    Model.fn.init = function() {
        this.sandboxSecretKey             = $( '[data-field="sandbox-secret-key"]' );
        this.sandboxPublicKey             = $( '[data-field="sandbox-public-key"]' );
        this.productionSecretKey          = $( '[data-field="production-secret-key"]' );
        this.productionPublicKey          = $( '[data-field="production-public-key"]' );
        this.installments                 = $( '[data-field="installments"]' );
        this.billet                       = $( '[data-field="billet"]' );
        this.installmentsMax              = $( '[data-field="installments-maximum"]' );
        this.installmentsInterest         = $( '[data-field="installments-interest"]' );
        this.installmentsByFlag           = $( '[data-field="installments-by-flag"]' );
        this.installmentsWithoutInterest  = $( '[data-field="installments-without-interest"]' );
        this.installmentsInterestIncrease = $( '[data-field="installments-interest-increase"]' );
        this.antifraudSection             = $( 'h3[id*="woo-pagarme-payments_section_antifraud"]' );
        this.antifraudEnabled             = $( '[data-field="antifraud-enabled"]' );
        this.antifraudMinValue            = $( '[data-field="antifraud-min-value"]' );
        this.ccBrands                     = $( '[data-field="flags-select"]' );
        this.ccAllowSave                  = $( '[data-field="cc-allow-save"]' );
        this.billetBank                   = $( '[data-field="billet-bank"]' );
        this.softDescriptor               = $( '[data-field="soft-descriptor"]' );

        this.isGatewayIntegrationType = $('input[id*="woo-pagarme-payments_is_gateway_integration_type"]').prop("checked");
        this.installmentsMaxByFlag = this.installmentsByFlag.find('input[name*="cc_installments_by_flag[max_installment]"]');
        this.installmentsWithoutInterestByFlag = this.installmentsByFlag.find('input[name*="cc_installments_by_flag[no_interest]"]');

        this.handleEnvironmentFieldsVisibility(this.elements.environmentSelect.val());
        this.handleInstallmentFieldsVisibility(this.elements.installmentsTypeSelect.val());
        this.handleGatewayIntegrationFieldsVisibility(this.isGatewayIntegrationType);
        this.handleBilletBankRequirement();

        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments();
        this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag();

        this.setInstallmentsByFlags( null, true );

        this.addEventListener();
    };

    Model.fn.addEventListener = function() {
        this.on( 'keyup', 'soft-descriptor' );
        this.on( 'change', 'environment' );
        this.on( 'change', 'installments-type' );
        this.on( 'change', 'is-gateway-integration-type' );
        this.on( 'change', 'enable-billet' );
        this.on( 'change', 'enable-multimethods-billet-card' );

        this.elements.flagsSelect.on( 'select2:unselecting', this._onChangeFlags.bind(this) );
        this.elements.flagsSelect.on( 'select2:selecting', this._onChangeFlags.bind(this) );

        $( '#mainform' ).on( 'submit', this._onSubmitForm.bind( this ) );
    };

    Model.fn._onKeyupSoftDescriptor = function( event ) {
        var isGatewayIntegrationType = $('input[id*="woo-pagarme-payments_is_gateway_integration_type"]').prop("checked");

        if (!isGatewayIntegrationType && event.currentTarget.value.length > 13) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        if (isGatewayIntegrationType && event.currentTarget.value.length > 24) {
            $(event.currentTarget).addClass(errorClass);
            return;
        }

        $( event.currentTarget ).removeClass( errorClass );
    };

    Model.fn._onSubmitForm = function( event ) {
        this.toTop = false;
        this.items = [];

        this.elements.validate.each( this._eachValidate.bind( this ) );

        return !~this.items.indexOf( true );
    };

    Model.fn._onChangeEnvironment = function( event ) {
        this.handleEnvironmentFieldsVisibility( event.currentTarget.value );
    };

    Model.fn._onChangeInstallmentsType = function( event ) {
        this.handleInstallmentFieldsVisibility( event.currentTarget.value );
    };

    Model.fn._onChangeIsGatewayIntegrationType = function(event) {
        this.handleGatewayIntegrationFieldsVisibility(event.currentTarget.checked);
    };

    Model.fn._onChangeEnableBillet = function() {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeEnableMultimethodsBilletCard = function() {
        this.handleBilletBankRequirement();
    };

    Model.fn._onChangeFlags = function( event ) {
        this.setInstallmentsByFlags( event, false );
    };

    Model.fn._eachValidate = function( index, field ) {
        var rect;
        var element = $( field )
          , empty   = element.isEmptyValue()
          , func    = empty ? 'addClass' : 'removeClass'
        ;

        if ( ! element.is( ':visible' ) ) {
            return;
        }

        element[func]( errorClass );

        this.items[index] = empty;

        if ( ! empty ) {
            return;
        }

        field.placeholder = field.dataset.errorMsg;

        if ( ! this.toTop ) {
            this.toTop = true;
            rect       = field.getBoundingClientRect();
            window.scrollTo( 0, ( rect.top + window.scrollY ) - 32 );
        }
    };

    Model.fn.handleEnvironmentFieldsVisibility = function( value ) {
        var sandboxPublicKeyContainer    = this.sandboxPublicKey.closest( 'tr' )
          , sandboxSecretKeyContainer 	 = this.sandboxSecretKey.closest( 'tr' )
          , productionPublicKeyContainer = this.productionPublicKey.closest( 'tr' )
          , productionSecretKeyContainer = this.productionSecretKey.closest( 'tr' )
        ;

        if ( value == 'sandbox' ) {
            sandboxPublicKeyContainer.show();
            sandboxSecretKeyContainer.show();
            productionPublicKeyContainer.hide();
            productionSecretKeyContainer.hide();
        } else {
            productionPublicKeyContainer.show();
            productionSecretKeyContainer.show();
            sandboxPublicKeyContainer.hide();
            sandboxSecretKeyContainer.hide();
        }
    };

    Model.fn.handleInstallmentFieldsVisibility = function( value ) {
        var installmentsMaxContainer      		  = this.installmentsMax.closest( 'tr' )
          , installmentsInterestContainer 		  = this.installmentsInterest.closest( 'tr' )
          , installmentsByFlagContainer   		  = this.installmentsByFlag.closest( 'tr' )
          , installmentsWithoutInterestContainer  = this.installmentsWithoutInterest.closest( 'tr' )
          , installmentsInterestIncreaseContainer = this.installmentsInterestIncrease.closest( 'tr' )
        ;

        if ( value == 1 ) {
            installmentsMaxContainer.show();
            installmentsInterestContainer.show();
            installmentsInterestIncreaseContainer.show();
            installmentsWithoutInterestContainer.show();
            installmentsByFlagContainer.hide();
        } else {
            if ( this.elements.flagsSelect.val() ) {
                installmentsByFlagContainer.show();
            }
            installmentsMaxContainer.hide();
            installmentsInterestContainer.hide();
            installmentsInterestIncreaseContainer.hide();
            installmentsWithoutInterestContainer.hide();
        }
    };

    Model.fn.getOnlyGatewayBrands = function() {
        this.aleloBrand = $( 'select[id*="woo-pagarme-payments_cc_flags"]' ).find('option[value="alelo"]');
        return 'option[value="credz"], ' +
            'option[value="sodexoalimentacao"], ' +
            'option[value="sodexocultura"], ' +
            'option[value="sodexogift"], ' +
            'option[value="sodexopremium"], ' +
            'option[value="sodexorefeicao"], ' +
            'option[value="sodexocombustivel"], ' +
            'option[value="vr"], ' +
            'option[value="alelo"], ' +
            'option[value="banese"], ' +
            'option[value="cabal"]';
    };

    Model.fn.getOnlyGatewayInstallments = function() {
        var installments = '';
        var maxInstallmentsLength = this.installmentsMax.children('option').length;

        for (let i = 13; i <= maxInstallmentsLength+1; i++) {
            if (i === 24) {
                installments += 'option[value="' + i + '"]';
                continue;
            }

            installments += 'option[value="' + i + '"], ';
        }

        return installments;
    };

    Model.fn.setOriginalSelect = function($select) {
        if ($select.data("originalHTML") === undefined) {
            $select.data("originalHTML", $select.html());
        }
    };

    Model.fn.removeOptions = function($select, $options) {
        this.setOriginalSelect($select);
        $options.remove();
    };

    Model.fn.restoreOptions = function($select) {
        var originalgHTML = $select.data("originalHTML");
        if (originalgHTML !== undefined) {
            $select.html(originalgHTML);
        }
    };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments = function () {
        var installmentsMaxElement = this.installmentsMax;
        setMaxInstallmentsWithoutInterest(installmentsMaxElement.val());

        installmentsMaxElement.on('change', function() {
            setMaxInstallmentsWithoutInterest($(this).val());
        });

        function setMaxInstallmentsWithoutInterest(installmentsMax) {
            var installmentsWithoutInterest = $('[data-field="installments-without-interest"]');
            installmentsWithoutInterest.children('option').hide();
            installmentsWithoutInterest.children('option').filter(function() {
                return parseInt($(this).val()) <= installmentsMax;
            }).show();
        }
    };

    Model.fn.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag = function () {
        var installmentsWithoutInterestByFlagElement = this.installmentsWithoutInterestByFlag;
        var installmentsMaxElement = this.installmentsMaxByFlag;

        setMaxInstallmentsWithoutInterest(installmentsMaxElement.val());

        installmentsMaxElement.on('change', function() {
            setMaxInstallmentsWithoutInterest($(this).val());
        });

        function setMaxInstallmentsWithoutInterest(installmentsMax) {
            installmentsWithoutInterestByFlagElement.prop("max", installmentsMax);
        }
    };

    Model.fn.handleGatewayIntegrationFieldsVisibility = function( value ) {
        var antifraudEnabled  = this.antifraudEnabled.closest( 'tr' )
          , antifraudMinValue = this.antifraudMinValue.closest( 'tr' )
          , ccAllowSave = this.ccAllowSave.closest( 'tr' )
          , billetBank = this.billetBank.closest( 'tr' )
        ;

        if (value == false) {
            antifraudEnabled.hide();
            antifraudMinValue.hide();
            ccAllowSave.hide();
            billetBank.hide();
            this.antifraudSection.hide();

            this.ccAllowSave.prop("checked", false);
            var $optionsToRemove = this.ccBrands.find(this.getOnlyGatewayBrands());
            this.removeOptions(this.ccBrands, $optionsToRemove);

            this.softDescriptor.prop('maxlength', 13);

            var $optionsToRemoveInstallments = this.installmentsMax.find(this.getOnlyGatewayInstallments());
            var $optionsToRemoveInstallmentsWithoutInterest = this.installmentsWithoutInterest.find(this.getOnlyGatewayInstallments());
            this.removeOptions(this.installmentsMax, $optionsToRemoveInstallments);
            this.removeOptions(this.installmentsWithoutInterest, $optionsToRemoveInstallmentsWithoutInterest);

            this.installmentsMaxByFlag.prop("max", 12);
            this.installmentsWithoutInterestByFlag.prop("max", 12);

            this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments();
            this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag();
        }

        if (value == true) {
            antifraudEnabled.show();
            antifraudMinValue.show();
            ccAllowSave.show();
            billetBank.show();
            this.antifraudSection.show();

            this.restoreOptions(this.ccBrands);

            this.softDescriptor.prop('maxlength', 22);

            this.restoreOptions(this.installmentsMax);
            this.restoreOptions(this.installmentsWithoutInterest);

            this.installmentsMaxByFlag.prop("max", 24);
            this.installmentsWithoutInterestByFlag.prop("max", 24);

            this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallments();
            this.setMaxInstallmentsWithoutInterestBasedOnMaxInstallmentsByFlag();
        }
    };

    Model.fn.handleBilletBankRequirement = function() {
        let bankRequirementFields = $( '[data-requires-field="billet-bank"]' );
        let billetBankElementId = '#woocommerce_woo-pagarme-payments_billet_bank';
        let billetBankIsRequired = false;

        bankRequirementFields.each(function() {
            if ( $( this ).prop( "checked" ) ) {
                billetBankIsRequired = true;
                return false;
            }
        });

        if ( billetBankIsRequired ) {
            $( billetBankElementId ).attr( 'required', true );
            return;
        }

        $( billetBankElementId ).attr( 'required', false );
    };

    Model.fn.setInstallmentsByFlags = function( event, firstLoad ) {
        var flags        = this.elements.flagsSelect.val() || [];
        var flagsWrapper = this.installmentsByFlag.closest( 'tr' );
        var allFlags = $('[data-flag]');

        if ( parseInt( this.elements.installmentsTypeSelect.val() ) !== 2 ) {
            allFlags.hide();
            flagsWrapper.hide();
            return;
        }

        if ( ! firstLoad ) {
            var selectedItem = event.params.args.data.id;
            var filtered     = flags;

            flagsWrapper.show();

            if ( event.params.name == 'unselect' ) {
                filtered = flags.filter(function(i) {
                    return i != selectedItem;
                });

                if ( filtered.length == 0 ) {
                    this.installmentsByFlag.closest( 'tr' ).hide();
                }
            } else {
                filtered.push( selectedItem );
            }

            allFlags.hide();

            filtered.map(function(item) {
                var element = $( '[data-flag=' + item + ']' );
                element.show();
            });
        } else {
            if ( flags.length === 0 ) {
                allFlags.hide();
                flagsWrapper.hide();
                return;
            }

            allFlags.each(function(index, item) {
                item = $(item);
                if ( ! flags.includes( item.data( 'flag' ) ) ) {
                    item.hide();
                } else {
                    item.show();
                }
            });
        }
    };

});


