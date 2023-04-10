const qrRawCodeTarget = '#pagarme-qr-code';
$ = jQuery;
let pagarmePix = {
    started: false,
    isStarted: function () {
        if (!this.started) {
            this.started = true;
            return false;
        }
        return true;
    },
    start: function () {
        // if (this.isStarted()) {
        //     return;
        // }
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
        try {
            swal(message);
        } catch (e) {
            new swal(message);
        }
    }
}
pagarmePix.start();
