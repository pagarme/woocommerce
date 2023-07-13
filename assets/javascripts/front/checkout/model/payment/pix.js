$ = jQuery;
let pagarmePix = {
    qrRawCodeTarget: '#pagarme-qr-code-button',
    start: function () {
        this.addEventListener();
    },
    addEventListener: function () {
        $(this.qrRawCodeTarget).on('click', function (e) {
            pagarmePix.copyRawCode();
        });
    },
    copyRawCode: function () {
        let elem = $(this.qrRawCodeTarget);
        if (!elem.length) {
            return;
        }
        let input = $('<input>').attr({
            value: elem.attr('rawCode')
        }).appendTo(elem.parent()).select();
        document.execCommand('copy', false);
        input.remove();
        const message = {
            type: 'success',
            html: 'Código copiado.',
            allowOutsideClick: false
        };
        swal(message);
    }
};
pagarmePix.start();
