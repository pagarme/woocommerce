MONSTER( 'Mundipagg.Application', function(Model, $, utils) {

	var createNames = [
	];

	Model.init = function(container) {
		Mundipagg.BuildComponents.create( container );
		Mundipagg.BuildCreate.init( container, createNames );
	};

});
