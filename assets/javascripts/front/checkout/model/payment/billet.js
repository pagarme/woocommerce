jQuery('#pagarme-billet-button').on('click', function(e){
    e.preventDefault();
    setTimeout(() => { // Safari is blocking any call to window.open() which is made inside an async call.
        window.open(jQuery(this).attr('data-pagarme-billet-url'), '_blank');
    });
});
