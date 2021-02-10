MONSTER( 'Pagarme.Application', function(Model, $, utils) {

	var createNames = [
	];

	Model.init = function(container) {
		Pagarme.BuildComponents.create( container );
		Pagarme.BuildCreate.init( container, createNames );
	};

});
