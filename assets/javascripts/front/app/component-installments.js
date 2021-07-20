MONSTER( 'Pagarme.Components.Installments', function(Model, $, utils) {
    Model.fn.start = function() {
		this.lock  = false;
		this.total = this.$el.data( 'total' );
		this.addEventListener();
	};

	Model.fn.addEventListener = function() {
		if ( this.$el.data( 'type' ) == 2 ) {
			$( 'body' ).on( 'pagarmeChangeBrand', this.onChangeBrand.bind(this) );
			$( 'body' ).on( 'pagarmeSelectOneClickBuy', this.onSelectOneClickBuy.bind(this) );
		}

		$( 'body' ).on( 'pagarmeBlurCardOrderValue', this.onBlurCardOrderValue.bind(this) );
	};

	Model.fn.onChangeBrand = function(event, brand, cardNumberLength, wrapper) {
		var cardOrderValue = wrapper.find( '[data-element=card-order-value]' );

		if ( cardOrderValue.length ) {
			this.total = cardOrderValue.val();
			this.total = this.total.replace( '.', '' );
			this.total = this.total.replace( ',', '.' );
		}

		if ( cardNumberLength >= 13 && cardNumberLength <= 19 ) {
			this.request( brand, this.total, wrapper );
		}
	};

	Model.fn.onSelectOneClickBuy = function(event, brand, wrapper) {
		this.request( brand, this.total, wrapper );
	};

	Model.fn.onBlurCardOrderValue = function(event, brand, total, wrapper) {
		this.request( brand, total, wrapper );
	};

	Model.fn.request = function(brand, total, wrapper) {
		var storageName = btoa( brand + total );
		var storage     = sessionStorage.getItem( storageName );
		var select 		= wrapper.find( '[data-element=installments]' );

		if ( storage ) {
			select.html( storage );
			return false;
		}

		var ajax = $.ajax({
			'url': MONSTER.utils.getAjaxUrl(),
			'data' : {
				'action': 'xqRhBHJ5sW',
				'flag': brand,
				'total': total
			}
		});

		ajax.done( $.proxy( this._done, this, select, storageName ) );
		ajax.fail( this._fail.bind(this) );

		if ( this.lock ) {
		    return;
		}

		this.lock = true;

		this.showLoader();

	};

	Model.fn._done = function(select, storageName, response) {
		this.lock = false;
		select.html(response);
		sessionStorage.setItem(storageName, response);
		this.removeLoader();
	};

	Model.fn._fail = function() {
		this.lock = false;
		this.removeLoader();
	};

	Model.fn.showLoader = function() {
		$('#wcmp-checkout-form').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
	};

	Model.fn.removeLoader = function() {
		$('#wcmp-checkout-form').unblock();
	};
});
