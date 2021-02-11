MONSTER( 'Pagarme.BuildComponents', function(Model, $, utils) {

	Model.create = function(container) {
		var components    = '[data-' + utils.addPrefix( 'component' ) + ']'
		  , findComponent = utils.findComponent.bind( container )
		;

		findComponent( components, $.proxy( this, '_start' ) );
	};

	Model._start = function(components) {
		if ( typeof Pagarme.Components === 'undefined' ) {
			return;
		}

		this._iterator( components );
	};

	Model._iterator = function(components) {
		var name;

		components.each( function(index, component) {
			component = $( component );
			name      = utils.ucfirst( this.getComponent( component ) );
			this._callback( name, component );
		}.bind( this ) );
	};

	Model.getComponent = function(component) {
		var component = component.data( utils.addPrefix( 'component' ) );

		if ( !component ) {
			return '';
		}

		return component;
	};

	Model._callback = function(name, component) {
		var callback = Pagarme.Components[name];

		if ( typeof callback == 'function' ) {
			callback.call( null, component );
			return;
		}

		console.log( 'Component "' + name + '" is not a function.' );
	};

}, {} );
