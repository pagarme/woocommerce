/* globals jquery, ajaxurl, pagarme_settings */
/* jshint esversion: 6 */
(   function ($) {
        $('.wc_gateways').on(
            'click',
            '.wc-payment-gateway-method-toggle-enabled, .pagarme-toggle-payment-subscription',
            function () {
                const $link = $(this),
                    $row = $link.closest('tr'),
                    $toggle = $link.find('.woocommerce-input-toggle');

                let action = 'woocommerce_toggle_gateway_enabled';
                if ($link.attr('class') === 'pagarme-toggle-payment-subscription') {
                    action = 'pagarme_toggle_payment_subscription';
                }

                const data = {
                    action: action,
                    security: pagarme_settings.nonces.gateway_toggle,
                    gateway_id: $row.data('gateway_id'),
                };

                $toggle.addClass('woocommerce-input-toggle--loading');

                $.ajax({
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    type: 'POST',
                    success: function (response) {
                        if (true === response.data) {
                            $toggle.removeClass(
                                'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled'
                            ).addClass(
                                'woocommerce-input-toggle--enabled'
                            ).removeClass(
                                'woocommerce-input-toggle--loading'
                            );
                            return;
                        }
                        if (false === response.data) {
                            $toggle.removeClass(
                                'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled'
                            ).addClass(
                                'woocommerce-input-toggle--disabled'
                            ).removeClass(
                                'woocommerce-input-toggle--loading'
                            );
                            return;
                        }
                        if ('needs_setup' === response.data) {
                            window.location.href = $link.attr('href');
                        }
                    },
                });
                return false;
            }
        );

        const allow_no_address_swal = pagarme_settings.allow_no_address_swal;
        $('#allow_no_address').on(
            'change',
            function () {
                const element = $(this);
                const value = element.val();
                if (value === 'yes') {
                    swal.fire({
                        type: 'warning',
                        title: allow_no_address_swal.title,
                        text: allow_no_address_swal.text,
                        showConfirmButton: true,
                        showDenyButton: true,
                        denyButtonText: allow_no_address_swal.cancelButtonText,
                        allowOutsideClick: false,
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            element.val('no');
                        }
                    });
                }
            }
        );
    }(jQuery)
);
