MONSTER( 'Mundipagg.Components.Settings', function(Model, $, Utils) {

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

		this.handleEnvironmentFieldsVisibility( this.elements.environmentSelect.val() );
		this.handleInstallmentFieldsVisibility( this.elements.installmentsTypeSelect.val() );

		this.setInstallmentsByFlags( null, true );

		this.addEventListener();
	};

	Model.fn.addEventListener = function() {
		this.on( 'keyup', 'soft-descriptor' );
		this.on( 'change', 'environment' );
		this.on( 'change', 'installments-type' );

		this.elements.flagsSelect.on( 'select2:unselecting', this._onChangeFlags.bind(this) );
		this.elements.flagsSelect.on( 'select2:selecting', this._onChangeFlags.bind(this) );

		$( '#mainform' ).on( 'submit', this._onSubmitForm.bind( this ) );
	};

	Model.fn._onKeyupSoftDescriptor = function( event ) {
		if ( event.currentTarget.value.length > 13 ) {
			$( event.currentTarget ).addClass( errorClass );
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
