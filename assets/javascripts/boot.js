jQuery(function($) {
	var context = $( 'body' );

	Pagarme.vars = {
		body : context
	};

	Pagarme.Application.init.apply( null, [context] );
});
