/* jshint esversion: 6 */
const removeCard = '[data-action="remove-card"]';

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
        swal.fire({
            title: walletConfig.dataSwal.confirm_title,
            text: walletConfig.dataSwal.confirm_text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: walletConfig.dataSwal.confirm_color,
            cancelButtonColor: walletConfig.dataSwal.cancel_color,
            confirmButtonText: walletConfig.dataSwal.confirm_button,
            cancelButtonText: walletConfig.dataSwal.cancel_button,
            allowOutsideClick: false,
        })
            .then((result) => {
                if (result.isConfirmed) {
                    pagarmeWallet._request(event.currentTarget.dataset.value);
                }
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
        }).done(this._done);
    },
    _done: function (response) {
        if (response.success) {
            pagarmeWallet.successMessage(response.data);
        } else {
            pagarmeWallet.failMessage(response.data);
        }
    },
    failMessage: function (message) {
        swal.fire({
            icon: 'error',
            html: message
        });
    },
    successMessage: function (message) {
        swal.fire({
            icon: 'success',
            html: message,
            allowOutsideClick: false
        }).then(function () {
            location.reload(true);
        });
    },
};

pagarmeWallet.start();
