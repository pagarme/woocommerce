const { __ } = wp.i18n;
jQuery(function ($) {
    $('.pagarme-get-hub-account-info').on('click', function (e) {
        try {
            swal({
                title: ' ',
                text: __('Processing', 'woo-pagarme-payments'),
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
                    swal(
                        __('Success!', 'woo-pagarme-payments'),
                        __('Dash configuration was retrieved successfully. The page is reloading. Please, wait a moment.',
                            'woo-pagarme-payments'
                        ),
                        'success'
                    )
                    document.location.reload(true);
                },
                fail: function (response) {
                    swal(
                        __('Fail!', 'woo-pagarme-payments'),
                        __('Dash configuration was not retrieved. Please, try again.', 'woo-pagarme-payments'),
                        'error'
                    )
                }

            });
        } catch (e) {
            swal(
                __('Fail!', 'woo-pagarme-payments'),
                __('Dash configuration was not retrieved. Please, try again.', 'woo-pagarme-payments'),
                'error'
            )
        }
    });
});
