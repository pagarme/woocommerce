MONSTER( 'Pagarme.BuildCreate', function(Model, $, utils) {

	Model.init = function(container, names) {
		if ( !names.length ) {
			return;
		}

		this.$el = container;
		names.forEach( this.findNames.bind( this ) );
	};

	Model.findNames = function(name, index) {
		this.callback( Pagarme[utils.ucfirst( name )] );
	};

	Model.callback = function(callback) {
		if ( typeof callback !== 'function' ) {
			return;
		}

		callback.create( this.$el );
	};

}, {} );
