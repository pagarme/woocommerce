MONSTER( 'Pagarme.Components.CheckoutTransparent', function(Model, $, utils) {

	Model.fn.start = function() {
		this.lock = false;

		this.addEventListener();

		Pagarme.CheckoutErrors.create( this );

		if ( typeof $().select2 === 'function' ) {
			this.applySelect2();
		}

        $('div#woo-pagarme-payment-methods > ul li input:first').attr('checked', 'checked');
        $('div#woo-pagarme-payment-methods > ul li:first').find('.payment_box').show();

        $('input#payment_method_woo-pagarme-payments').click(function() {
            $('div#woo-pagarme-payment-methods').removeAttr('style');

            $('div#woo-pagarme-payment-methods > ul li').find(function() {
                $('input:checked:last').nextAll().show();
            });
        });

        $('input[name=method]').change(function(e) {
            e.stopPropagation();
            var li = e.target.closest('li');
            $('.pagarme_methods').slideUp('slow');
            $(li).find('.payment_box').slideDown('slow');
        });
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

		if ( this.elements.enableMulticustomers ) {
			this.elements.enableMulticustomers.on( 'click', this.handleMultiCustomers )
		}
	};

	Model.fn._onSubmit = function(e) {
		e.preventDefault();

		if ( ! this.validate() ) {
			jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
			return false;
		}

		$( 'body' ).on( 'onPagarmeCheckoutDone', function(){
			if ( $( 'input[name=payment_method]' ).val() == '2_cards' ) {
				return;
			}
		}.bind(this));

		$( 'body' ).on( 'onPagarme2CardsDone', function(){
			if ( window.Pagarme2Cards === 2 ) {
				this.loadSwal();
			}
			window.Pagarme2Cards = 0;
		}.bind(this));

		jQuery('#wcmp-submit').attr('disabled', 'disabled');

		$( 'body' ).trigger( 'onPagarmeSubmit', [ e ] )

		if (
            $('input[name=payment_method]').val() === 'billet' ||
            $('input[name=payment_method]').val() === 'pix' ) {
			this.loadSwal();
		}


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

		var total = e.target.value;

		if ( total ) {
			total = total.replace( '.', '' );
			total = total.replace( ',', '.' );

			var brand = wrapper.find( '[data-element="choose-credit-card"]' ).find( 'option:selected' ).data( 'brand' );

			$( 'body' ).trigger( "pagarmeBlurCardOrderValue", [ brand, total, wrapper ] );
		} else {
			wrapper.find( '[data-element=installments]' ).html( option );
		}
	};

	Model.fn._done = function(response) {
		this.lock = false;
		if ( ! response.success ) {
		    this.failMessage(
				this.getFailMessage(response.data)
			);
		} else {
			this.successMessage();
		}

        var self = this;

		if( response.data.status == "failed" ){
            swal({
                type : 'error',
                html : this.getFailMessage()
            }).then(function(){
                window.location.href = self.data.returnUrl;
            });
		}
	};

	Model.fn._fail = function(jqXHR, textStatus, errorThrown) {
		this.lock = false;
		this.failMessage();
	};

	Model.fn.getFailMessage = function (message = "") {
		if (!message) {
			return "Transação não autorizada."
		}

		return message;
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
		this.$el.byAction( 'select2' ).select2({
			width: '100%',
			minimumResultsForSearch: 20
		});

		this.$el.find('[data-element=state]').select2({
			width: '100%',
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

	Model.fn.handleMultiCustomers = function(e) {
		var input = $(e.currentTarget);
		var method = input.is(':checked') ? 'slideDown' : 'slideUp';
		var target = '[data-ref="' + input.data('target') + '"]';
		$( target )[method]();
	}

	Model.fn.validate = function() {
		var requiredFields = $( '[data-required=true]:visible' )
		  , isValid = true
		;

		requiredFields.each(function(index, item){
			var field = $( item );
			if ( ! $.trim( field.val() ) ) {
				if ( field.attr( 'id' ) == 'installments' ) {
					field = field.next();
				}
				field.addClass( 'invalid' );
				isValid = false;
			}
		});

		return isValid;
	};

	Model.fn._onOpenSwal = function () {
		if (this.lock) {
			return;
		}

		this.lock = true;

		swal.showLoading();

		var inputsSubmit = this.$el.serializeArray();

		this.ajax({
			url: this.data.apiRequest,
			data: {
				order: this.data.order,
				fields: inputsSubmit
			},
			success: function (data) {
				if (data.success == false) {
					jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
				}
			},
			fail: function (data) {
				jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');
			}
		});
	};

	Model.fn._onChangeCreditCard = function(event) {
		var select  = $( event.currentTarget );
		var wrapper = select.closest( 'fieldset' );
		var method  = event.currentTarget.value.trim() ? 'slideUp': 'slideDown';
		var type    = method == 'slideUp' ? 'OneClickBuy': 'DefaultBuy';
		var brandInput = wrapper.find( '[data-pagarmecheckout-element="brand-input"]' );

		$( '#wcmp-checkout-errors' ).hide();

		$( 'body' ).trigger( "onPagarmeCardTypeChange", [ type, wrapper ] );

		var brand = select.find('option:selected').data('brand');
		brandInput.val(brand);

		if ( select.data( 'installments-type' ) == 2 ) {

			if ( type == 'OneClickBuy' ) {
				$( 'body' ).trigger( 'pagarmeSelectOneClickBuy', [ brand, wrapper ] );
			} else {
				brandInput.val( '' );
				var option = '<option value="">...</option>';
				$( '[data-element=installments]' ).html( option );
			}
		}

		wrapper.find( '[data-element="fields-cc-data"]' )[method]();
		wrapper.find( '[data-element="fields-voucher-data"]' )[method]();
		wrapper.find( '[data-element="save-cc-check"]' )[method]();
		wrapper.find( '[data-element="enable-multicustomers-check"]' )[method]();
		wrapper.find( '[data-element="enable-multicustomers-label-card"]' )[method]();
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

	Model.fn.isTwoCardsPayment = function(firstInput, secondInput){
		return firstInput.id.includes("card") && secondInput.id.includes("card");
	};

	Model.fn.isBilletAndCardPayment = function(firstInput, secondInput){
		return (firstInput.id.includes("card") && secondInput.id.includes("billet")) ||
		(firstInput.id.includes("billet") && secondInput.id.includes("card"));
	};

	Model.fn.refreshBothInstallmentsSelects = function(event, secondInput){
		this._onBlurCardOrderValue(event);
		event.currentTarget = secondInput;
		event.target = secondInput;

		this._onBlurCardOrderValue(event);
	};

	Model.fn.refreshCardInstallmentSelect = function(event, secondInput){
		const targetInput = event.target.id.includes("card") ? event.target : secondInput;

		event.currentTarget = targetInput;
		event.target = targetInput;

		this._onBlurCardOrderValue(event);
	}

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

		value = value.toFixed(2);
		value = value.replace('.', ',');

		nextInput.val(nextValue);
		input.val(value);

		if ( this.isTwoCardsPayment(event.target, nextInput[0]) ){
		    this.refreshBothInstallmentsSelects(event, nextInput[0]);
		}

		if( this.isBilletAndCardPayment(event.target, nextInput[0]) ){
		    this.refreshCardInstallmentSelect(event, nextInput[0]);
		}
	};

});
