/*jshint esversion: 6 */
let pagarmePix = {
    qrRawCodeTarget: '#pagarme-qr-code-button',
    start: function () {
        this.addEventListener();
    },
    addEventListener: function () {
        jQuery(this.qrRawCodeTarget).on('click', function (e) {
            pagarmePix.copyRawCode();
        });
    },
    copyRawCode: function () {
        let elem = jQuery(this.qrRawCodeTarget);
        if (!elem.length) {
            return;
        }
        const rawCode = elem.attr('rawCode');
        const message = {
            icon: 'success',
            text: 'Código copiado.'
        };

        if (window.isSecureContext && navigator.clipboard) {
            navigator.clipboard.writeText(rawCode);
            swal.fire(message);
            return;
        }

        const input = jQuery('<input>').attr({
            value: rawCode
        }).appendTo(elem.parent());

        const [ inputDOMElement ] = input;
        inputDOMElement.select();
        inputDOMElement.setSelectionRange(0, input.val().length);

        document.execCommand('copy', false);
        input.remove();
        swal.fire(message);
    }
};
pagarmePix.start();
