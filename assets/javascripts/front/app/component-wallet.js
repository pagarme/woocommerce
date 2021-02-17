MONSTER( 'Pagarme.Components.Wallet', function(Model, $, utils) {

	Model.fn.start = function() {
		this.addEventListener();
	};

	Model.fn.addEventListener = function() {
		this.click( 'remove-card' );
	};

	Model.fn._onClickRemoveCard = function(event) {
		event.preventDefault();

		swal({
		  title              : this.data.swal.confirm_title,
		  text               : this.data.swal.confirm_text,
		  type               : 'warning',
		  showCancelButton   : true,
		  confirmButtonColor : this.data.swal.confirm_color,
		  cancelButtonColor  : this.data.swal.cancel_color,
		  confirmButtonText  : this.data.swal.confirm_button,
		  cancelButtonText   : this.data.swal.cancel_button,
		  allowOutsideClick  : false,
		}).then( this._request.bind( this, event.currentTarget.dataset.value ), function() {} );
	};

	Model.fn._request = function(cardId) {
    	swal.showLoading();

	    this.ajax({
	    	url  : this.data.apiRequest,
			data : {
				card_id : cardId
			}
		});
	};

	Model.fn._done = function(response) {
		if ( response.success ) {
			this.successMessage( response.data );
		} else {
			this.failMessage( response.data );
		}
	};

	Model.fn._fail = function(jqXHR, textStatus, errorThrown) {

	};

	Model.fn.failMessage = function(message) {
		swal({
			type : 'error',
			html : message
		}).then(function() {});
	};

	Model.fn.successMessage = function(message) {
		swal({
			type              : 'success',
			html              : message,
			allowOutsideClick : false
		}).then(function(){
			window.location.reload(true);
		});
	};

});
