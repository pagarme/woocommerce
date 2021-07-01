MONSTER( 'Pagarme.Components.Capture', function(Model, $, Utils) {

	Model.fn.start = function() {
		this.init();
		this.lock = false;
	};

	Model.fn.init = function() {
		this.addEventListener();
		this.startModal();
	};

	Model.fn.addEventListener = function() {
		this.$el.find( '[data-ref]:enabled' ).on( 'click', function(e){
			e.preventDefault();
			var target   = e.currentTarget;
			var selector = '[data-charge-action=' + target.dataset.ref + '-' + target.dataset.type + ']';
			$( selector ).iziModal( 'open' );
		});
	};

	Model.fn.onClickCancel = function(e) {
		e.preventDefault();
		this.handleEvents( e, 'cancel' );
	};

	Model.fn.onClickCapture = function(e) {
		e.preventDefault();
		this.handleEvents( e, 'capture' );
	};

	Model.fn.handleEvents = function(DOMEvent, type) {
		var target   = $( DOMEvent.currentTarget );
		var wrapper  = target.closest( '[data-charge]' );
		var chargeId = wrapper.data( 'charge' );
		var amount   = wrapper.find( '[data-element=amount]' ).val();

		this.request( type, chargeId, amount);
	};

	Model.fn.request = function(mode, chargeId, amount) {
		if ( this.lock ) {
			return;
		}
		this.lock = true;
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
				var amount = modal.$element.find( '[data-element=amount]' );
                const options = {
                    reverse:true,
                    onKeyPress: function(amountValue, event, field){
                        if (!event.originalEvent){
                            return;
                        }

                        amountValue = amountValue.replace(/^0+/, '')
                        if (amountValue[0] === ','){
                            amountValue = '0' + amountValue;
                        }

                        if (amountValue && amountValue.length <= 2){
                            amountValue = ('000'+amountValue).slice(-3);
                            field.val(amountValue);
                            field.trigger('input');
                            return;
                        }

                        field.val(amountValue);
                    }
                };
                amount.mask( "#.##0,00", options );
				modal.$element.on( 'click', '[data-action=capture]', self.onClickCapture.bind(self) );
				modal.$element.on( 'click', '[data-action=cancel]', self.onClickCancel.bind(self) );
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
		this.lock = false;
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
		this.lock = false;
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
