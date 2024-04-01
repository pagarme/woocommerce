/* jshint esversion: 6 */
jQuery(function ($) {
    $('.pagarme-get-hub-account-info').on('click', function (e) {
        try {
            swal.fire({
                title: 'Processando',
                icon: 'warning',
                allowOutsideClick: false
            });
            swal.showLoading();
            $.ajax({
                url: pagarmeNotice.accountInfoUrl,
                type: 'POST',
                dataType: "json",
                data: JSON.stringify({
                    command: 'get'
                }),
                success: function (response) {
                    swal.fire({
                        title: 'Sucesso!',
                        text: 'As configurações da Dash foram recuperadas com sucesso. A página está recarregando.' +
                            ' Por favor, aguarde um momento.',
                        icon: 'success'
                    });
                    document.location.reload(true);
                },
                fail: function (response) {
                    swal.fire({
                        title: 'Falha!',
                        text: 'As configurações da Dash não foram recuperadas. Por favor, tente novamente.',
                        icon: 'error'
                    });
                }

            });
        } catch (error) {
            swal.fire({
                title: 'Falha!',
                text: 'As configurações da Dash não foram recuperadas. Por favor, tente novamente.',
                icon: 'error'
            });
        }
    });
});
