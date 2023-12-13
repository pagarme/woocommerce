const removeCard = '[data-action="remove-card"]'

let pagarmeWallet = {
    start: function () {
        this.addEventListener();
    },
    addEventListener: function () {
        jQuery(removeCard).click(function (e) {
            pagarmeWallet._onClickRemoveCard(e);

        });
    },
    _onClickRemoveCard: function (event) {
        event.preventDefault();
        swal({
            title: walletConfig.dataSwal.confirm_title,
            text: walletConfig.dataSwal.confirm_text,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: walletConfig.dataSwal.confirm_color,
            cancelButtonColor: walletConfig.dataSwal.cancel_color,
            confirmButtonText: walletConfig.dataSwal.confirm_button,
            cancelButtonText: walletConfig.dataSwal.cancel_button,
            allowOutsideClick: false,
        }).then(this._request.bind(this, event.currentTarget.dataset.value), function () {
        });
        },
    _request: function (cardId) {
        swal.showLoading();
        jQuery.ajax({
            method: 'post',
            url: walletConfig.apiRoute,
            data: {
                card_id: cardId
            }
        })
        .done(this._done)
    },
    _done: function (response) {
        if (response.success) {
            pagarmeWallet.successMessage(response.data);
        } else {
            pagarmeWallet.failMessage(response.data);
        }
    },
    _fail: function (jqXHR, textStatus, errorThrown) {
    },
    failMessage: function (message) {
        swal({
            type: 'error',
            html: message
        }).then(function () {
        });
    },
    successMessage: function (message) {
        swal({
            type: 'success',
            html: message,
            allowOutsideClick: false
        }).then(function () {
            location.reload(true);
        });
    },
}

pagarmeWallet.start();
