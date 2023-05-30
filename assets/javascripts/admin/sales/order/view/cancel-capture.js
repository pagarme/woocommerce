$ = jQuery;
const actionsTarget = '[data-ref]:enabled';
const modalTarget = '.modal';
const amountTarget = '[data-element=amount]';

let pagarmeCancelCapture = {
    started: false,
    lock:false,

    isStarted: function (){
        if (!this.started){
            this.started = true;
            return false;
        }
        return true;
    },

    start: function () {
        if (this.isStarted()) {
            return;
        }
        this.startModal();
        this.addEventListener();
    },

    startModal: function () {
        let self = this;
        $(modalTarget).iziModal({
            padding: 20,
            onOpening: function (modal) {
                let amount = modal.$element.find( amountTarget );
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
    },

    onClickCapture: function (e) {
        e.preventDefault();
        this.handleEvents( e, 'capture' );
    },

    onClickCancel: function (e) {
        e.preventDefault();
        this.handleEvents( e, 'cancel' );
    },

    handleEvents: function (DOMEvent, type) {
        let target   = $( DOMEvent.currentTarget );
        let wrapper  = target.closest( '[data-charge]' );
        let chargeId = wrapper.data( 'charge' );
        let amount   = wrapper.find( '[data-element=amount]' ).val();
        this.request( type, chargeId, amount);
    },

    request: function (mode, chargeId, amount) {
        if ( this.lock ) {
            return;
        }
        this.lock = true;
        this.requestInProgress();
        let ajax = $.ajax({
            'url': this.getAjaxUrl(),
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
    },

    openModal: function (e) {
        e.preventDefault();
        let target = e.currentTarget;
        let selector = '[data-charge-action=' + target.dataset.ref + '-' + target.dataset.type + ']';
        $(selector).iziModal('open');
    },

    requestInProgress: function () {
        swal({
            title: ' ',
            text: 'Processando...',
            allowOutsideClick: false
        });
        swal.showLoading();
    },

    _onDone: function(response) {
        this.lock = false;
        $( modalTarget ).iziModal('close');
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
    },

    getAjaxUrl: function() {
        return window.pagarme_settings.ajax_url;
    },

    _onFail: function(xhr) {
        this.lock = false;
        swal.close();
        let data = JSON.parse(xhr.responseText);
        swal({
            type: 'error',
            title: ' ',
            html: data.message,
            showConfirmButton: false,
            timer: 2000
        });
    },

    addEventListener: function () {
        $(actionsTarget).on('click', function (e) {
            pagarmeCancelCapture.openModal(e);
        });
    },
}
