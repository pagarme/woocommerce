MONSTER( 'Mundipagg.Components.CheckoutTransparent', function(Model, $, utils) {

	Model.fn.start = function() {
		this.addEventListener();

		Mundipagg.CheckoutErrors.create( this );

		if ( typeof $().select2 === 'function' ) {
			this.applySelect2();
		}
	};

	Model.fn.addEventListener = function() {
		this.$el.on( 'submit', this._onSubmit.bind(this) );
		this.$el.find( '[data-value]' ).on( 'blur', this.fillAnotherInput.bind(this) );
		this.click( 'tab' );
		this.click( 'choose-payment' );

		if ( this.elements.cardHolderName ) {
			this.elements.cardHolderName.on( 'keypress', this.removeSpecialChars );
			this.elements.cardHolderName.on( 'blur', this.removeSpecialChars );
		}

		if ( this.elements.chooseCreditCard ) {
			this.elements.chooseCreditCard.on( 'change', this._onChangeCreditCard.bind( this ) );
		}

		$( '[data-required=true]' ).on( 'keypress', this.setAsValid );
		$( '[data-required=true]' ).on( 'blur', this.setAsValid );

		if ( this.elements.cardOrderValue ) {
			this.elements.cardOrderValue.on( 'blur', this._onBlurCardOrderValue.bind( this ) );
		}

		if ( this.elements.cardNumber ) {
			this.elements.cardNumber.on( 'keyup', this.updateInstallments );
			this.elements.cardNumber.on( 'keydown', this.updateInstallments );
		}
	};

	Model.fn._onSubmit = function(e) {
		e.preventDefault();

		if ( ! this.validate() ) {
			return false;
		}

		if ( $('input[name=payment_method]').val() === 'billet' ) {
			this.loadSwal();
		}

		$( 'body' ).on( 'onMundiPaggCheckoutDone', function(){
			if ( $( 'input[name=payment_method]' ).val() == '2_cards' ) {
				return;
			}
			this.loadSwal();
		}.bind(this));
		
		$( 'body' ).on( 'onMundiPagg2CardsDone', function(){
			if ( window.MundiPagg2Cards === 2 ) {
				this.loadSwal();
			}
			window.MundiPagg2Cards = 0;
		}.bind(this));
	};

	Model.fn._onClickTab = function(event) {
		window.location = this.data.paymentUrl + '&tab=' + event.currentTarget.dataset.ref + $( event.currentTarget ).attr('href');
	};

	Model.fn._onClickChoosePayment = function(e) {
		var target = $( e.currentTarget );
		var forms  = $( '.wc-credit-card-form' );

		forms.attr( 'disabled', true );
		target.prev().removeAttr( 'disabled' );
	};

	Model.fn._onBlurCardOrderValue = function(e) {
		var option  = '<option value="">...</option>';
		var wrapper = $( e.currentTarget ).closest( 'fieldset' );
		
		if ( ! this.hasCardId( wrapper ) )  {
			wrapper.find( '[data-element=installments]' ).html( option );
			return;
		}
		
		var total = e.target.value;
		
		if ( total ) {
			total = total.replace( '.', '' );
			total = total.replace( ',', '.' );

			var brand = wrapper.find( '[data-element="choose-credit-card"]' ).find( 'option:selected' ).data( 'brand' );

			$( 'body' ).trigger( "mundipaggBlurCardOrderValue", [ brand, total, wrapper ] );
		} else {
			wrapper.find( '[data-element=installments]' ).html( option );
		}
	};

	Model.fn._done = function(response) {
		if ( ! response.success ) {
			this.failMessage( response.data );
		} else {
			this.successMessage();
		}
	};

	Model.fn._fail = function(jqXHR, textStatus, errorThrown) {
		this.failMessage();
	};

	Model.fn.failMessage = function(message) {
		swal({
			type : 'error',
			html : message || this.data.swal.text_default
		});
	};

	Model.fn.successMessage = function() {
		var self = this;

		swal({
			type : 'success',
			html : this.data.swal.text_success,
			allowOutsideClick : false
		}).then(function(){
			window.location.href = self.data.returnUrl;
		});
	};

	Model.fn.applySelect2 = function() {
		this.$el.byAction( 'select2' ).select2( {
			width: '400px',
			minimumResultsForSearch: 20
		});
	};

	Model.fn.removeSpecialChars = function() {
		this.value = this.value.replace( /[^a-zA-Z ]/g, "" );
	};

	Model.fn.setAsValid = function() {
		if ( this.value ) {
			$(this).removeClass( 'invalid' );
		}
	};

	Model.fn.updateInstallments = function(e) {
		if ( ! this.value ) {
			var option = '<option value="">...</option>';
			var select = $( e.currentTarget )
				.closest( 'fieldset' )
				.find('[data-element=installments]' )
			;

			if ( select.data( 'type' ) == 2 ) {
				select.html( option );
			}
		}
	};

	Model.fn.validate = function() {
		var requiredFields = $( '[data-required=true]:visible' )
		  , isValid = true
		;

		requiredFields.each(function(index, item){
			var field = $( item );
			if ( ! $.trim( field.val() ) ) {
				if ( field.attr( 'id' ) == 'installments' ) {
					field = field.next(); //Select2 span
				}
				field.addClass( 'invalid' );
				isValid = false;
			}
		});

		return isValid;
	};

	Model.fn.loadSwal = function() {
		swal.close();

		swal({
			title             : this.data.swal.title,
			text              : this.data.swal.text,
			allowOutsideClick : false,
			onOpen            : this._onOpenSwal.bind( this )
		});
	};

	Model.fn._onOpenSwal = function() {
    	swal.showLoading();

	    this.ajax({
	    	url  : this.data.apiRequest,
			data : {
				order  : this.data.order,
				fields : this.$el.serializeArray()
			}
		});
	};

	Model.fn._onChangeCreditCard = function(event) {
		var select  = $( event.currentTarget );
		var wrapper = select.closest( 'fieldset' );
		var method  = event.currentTarget.value.trim() ? 'slideUp': 'slideDown';
		var type    = method == 'slideUp' ? 'OneClickBuy': 'DefaultBuy';

		$( '#wcmp-checkout-errors' ).hide();

		$( 'body' ).trigger( "onMundipaggCardTypeChange", [ type, wrapper ] );

		if ( select.data( 'installments-type' ) == 2 ) {

			if ( type == 'OneClickBuy' ) {
				var brand = select.find( 'option:selected' ).data( 'brand' );
				$( 'body' ).trigger( 'mundipaggSelectOneClickBuy', [ brand, wrapper ] );
			} else {
				var option = '<option value="">...</option>';
				$( '[data-element=installments]' ).html( option );
			}
		}

		wrapper.find( '[data-element="fields-cc-data"]' )[method]();
		wrapper.find( '[data-element="save-cc-check"]' )[method]();
	};

	Model.fn.hasCardId = function(wrapper) {
		var element = wrapper.find( '[data-element="choose-credit-card"]' );

		if ( element === undefined || element.length === 0 ) {
			return false;
		}

		return element.val().trim() !== '';
	};

	Model.fn.requestInProgress = function() {
		swal({
			title             : this.data.swal.title,
			text              : this.data.swal.text,
			allowOutsideClick : false
		});
		swal.showLoading();
	};

	Model.fn.fillAnotherInput = function(event) {
		var input = $(event.currentTarget);
		var nextIndex = input.data('value') == 2 ? 1 : 2;
		var nextInput = $('[data-value=' + nextIndex + ']');
		var value = event.currentTarget.value;
		var total = parseFloat( this.data.orderTotal );

		if ( ! value ) {
			return;
		}
		
		value = value.replace('.', '');
		value = parseFloat( value.replace(',', '.') );

		var nextValue = total - value;
		
		if ( value > total ) {
			swal({
				type: 'error',
				text: 'O valor não pode ser maior que total do pedido!'
			});
			input.val('');
			nextInput.val('');
			return;
		}

		nextValue = nextValue.toFixed(2);
		nextValue = nextValue.replace('.',',');

		nextInput.val(nextValue);
	};

});
