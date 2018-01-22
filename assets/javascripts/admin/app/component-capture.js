MONSTER( 'Mundipagg.Components.Capture', function(Model, $, Utils) {

	Model.fn.start = function() {
		this.init();
	};

	Model.fn.init = function() {
		this.addEventListener();
		this.startModal();
	};

	Model.fn.addEventListener = function() {
		this.$el.find( '[data-ref]' ).on( 'click', function(e){
			e.preventDefault();
			$( '[data-charge=' + e.currentTarget.dataset.ref + ']' ).iziModal( 'open' );
		});

		this.$el.find( '[data-action=cancel]' ).on( 'click', this.onClickCancel.bind(this) );
	};

	Model.fn.onClickCancel = function(e) {
		e.preventDefault();
		var target   = $( e.currentTarget );
		var chargeId = target.next().data( 'ref' );

		this.request( 'cancel', chargeId, 0 );
	};

	Model.fn.onClickCapture = function(e) {
		e.preventDefault();
	
		var target   = $( e.currentTarget );
		var wrapper  = target.closest( '[data-charge]' );
		var chargeId = wrapper.data( 'charge' );
		var amount   = wrapper.find( '[data-element=amount]' ).val();

		this.request( 'capture', chargeId, amount );
	};

	Model.fn.request = function(mode, chargeId, amount) {
		this.requestInProgress();
		var ajax = $.ajax({
			'url': MONSTER.utils.getAjaxUrl(),
			'method': 'POST',
			'data': {
				'action': 'STW3dqRT6E',
				'mode': mode,
				'charge_id': chargeId,
				'amount': amount
			}
		});

		ajax.done( this._onDone.bind(this) );
		ajax.fail( this._onFail.bind(this) );
	};

	Model.fn.startModal = function() {
		var self = this;
		$( '.modal' ).iziModal({
			padding: 20,
			onOpening: function (modal) {
				var amount = self.$el.find( '[data-element=amount]' );
				amount.mask( "#.##0,00", { reverse: true } );
				modal.$element.on( 'click', '[data-action=capture]', self.onClickCapture.bind(self) );
			},
			onClosing: function (modal) {
				console.log( 'Fechou', modal );
			}
		});
	};

	Model.fn.requestInProgress = function () {
		swal({
			title: ' ',
			text: 'Processando...',
			allowOutsideClick: false
		});
		swal.showLoading();
	};

	Model.fn._onDone = function(response) {
		$( '.modal' ).iziModal('close');
		swal.close();
		swal({
			type: 'success',
			title: ' ',
			html: response.data.message,
			showConfirmButton: false,
			timer: 2000
		}).then(
			function () { },
			function (dismiss) {
				window.location.reload();
			}
		);
	};

	Model.fn._onFail = function(xhr) {
		swal.close();
		var data = JSON.parse(xhr.responseText);
		swal({
			type: 'error',
			title: ' ',
			html: data.message,
			showConfirmButton: false,
			timer: 2000
		});
	};

});
