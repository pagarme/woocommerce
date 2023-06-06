const qrRawCodeTarget = '#pagarme-qr-code-button';
$ = jQuery;
let pagarmePix = {
    start: function () {
        this.addEventListener();
    },
    addEventListener: function () {
        $(qrRawCodeTarget).on('click', function (e) {
            pagarmePix.copyRawCode();
        });
    },
    copyRawCode: function () {
        let elem = $(qrRawCodeTarget)
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
}
pagarmePix.start();
