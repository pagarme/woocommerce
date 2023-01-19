/* globals jQuery, ajaxurl, woocommerce_admin_scripts */
(function ($) {

    $('.wc_gateways').on(
        'click',
        '.wc-payment-gateway-method-toggle-enabled',
        function () {
            var $link = $(this),
                $row = $link.closest('tr'),
                $toggle = $link.find('.woocommerce-input-toggle');

            var data = {
                action: 'woocommerce_toggle_gateway_enabled',
                security: woocommerce_admin_scripts.nonces.gateway_toggle,
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
                        );
                        $toggle.addClass(
                            'woocommerce-input-toggle--enabled'
                        );
                        $toggle.removeClass(
                            'woocommerce-input-toggle--loading'
                        );
                    } else if (false === response.data) {
                        $toggle.removeClass(
                            'woocommerce-input-toggle--enabled, woocommerce-input-toggle--disabled'
                        );
                        $toggle.addClass(
                            'woocommerce-input-toggle--disabled'
                        );
                        $toggle.removeClass(
                            'woocommerce-input-toggle--loading'
                        );
                    } else if ('needs_setup' === response.data) {
                        window.location.href = $link.attr('href');
                    }
                },
            });

            return false;
        }
    );

})(jQuery);
