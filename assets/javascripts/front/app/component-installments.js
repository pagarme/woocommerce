MONSTER( 'Mundipagg.Components.Installments', function(Model, $, utils) {
    Model.fn.start = function() {
		this.total = this.$el.data( 'total' );

		this.addEventListener();
	};

	Model.fn.addEventListener = function() {
		if ( this.$el.data( 'type' ) == 2 ) {
			$( 'body' ).on( 'mundipaggChangeBrand', this.onChangeBrand.bind(this) );
			$( 'body' ).on( 'mundipaggSelectOneClickBuy', this.onSelectOneClickBuy.bind(this) );
		}

		$( 'body' ).on( 'mundipaggBlurCardOrderValue', this.onBlurCardOrderValue.bind(this) );

	};

	Model.fn.onChangeBrand = function(event, brand, cardNumberLength, wrapper) {
		var cardOrderValue = wrapper.find( '[data-element=card-order-value]' );
		
		if ( cardOrderValue.length ) {
			this.total = cardOrderValue.val();
			this.total = this.total.replace( '.', '' );
			this.total = this.total.replace( ',', '.' );
		}

		if ( cardNumberLength >= 13 ) {
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

		var self = this;

		ajax.done(function(response){
			select.html( response );
			sessionStorage.setItem( storageName, response );
		});
	};
});    