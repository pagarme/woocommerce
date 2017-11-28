jQuery(function($) {
	var context = $( 'body' );

	Mundipagg.vars = {
		body : context
	};

	Mundipagg.Application.init.apply( null, [context] );
});
