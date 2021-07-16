MONSTER( 'Pagarme.CheckoutErrors', function(Model, $, utils) {

	Model.create = function(context) {
		this.context = context;
		this.init();
	};

	Model.init = function() {
		$( 'body' ).on( 'onPagarmeCheckoutFail', this.error.bind(this) );
	};

	Model.error = function(event, errorThrown) {
		var error, rect;
		var element = $( '#wcmp-checkout-errors' );

		swal.close();

		this.errorList = '';

		for ( error in errorThrown.errors ) {
			(errorThrown.errors[error] || []).forEach( this.parseErrorsList.bind( this, error ) );
		}

		element.find( '.woocommerce-error' ).html( this.errorList );
		element.slideDown();

		rect = element.get(0).getBoundingClientRect();

		jQuery('#wcmp-submit').removeAttr('disabled', 'disabled');

		window.scrollTo( 0, ( rect.top + window.scrollY ) - 40 );
	};

	Model.parseErrorsList = function(error, message, index) {
		this.errorList += '<li>' + this.translateErrors( error, message ) + '<li>';
	};

	Model.translateErrors = function( error, message ) {
		error            = error.replace( 'request.', '' );
		var output       = error + ': ' + message;
		var ptBrMessages = PagarmeGlobalVars.checkoutErrors.pt_BR;

		if ( PagarmeGlobalVars.WPLANG != 'pt_BR' ) {
			return output;
		}

		if ( ptBrMessages.hasOwnProperty( output ) ) {
			return ptBrMessages[ output ];
		}

		return output;
	};

});
