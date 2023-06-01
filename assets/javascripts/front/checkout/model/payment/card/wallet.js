/* globals wc_pagarme_checkout */
$ = jQuery;
const cardSaveTarget = 'select[data-element="choose-credit-card"]';
const cardFields = [
    '[data-pagarmecheckout-element="fields-cc-data"]',
    '[data-element="save-cc-check"]',
    '[data-element="enable-multicustomers-check"]'
];
let pagarmeCheckoutWallet = {
    started: false,
    isStarted: function (){
        if (!this.started){
            this.started = true;
            return false;
        }
        return true;
    },
    onChangeCard: function (e) {
        let select  = $( e.currentTarget );
        let wrapper = select.closest( 'fieldset' );
        let method  = select.val() ? 'slideUp': 'slideDown';
        let brand = select.find('option:selected').data('brand');
        let brandInput = wrapper.find(pagarmeCard.getBrandTarget());
        brandInput.val(brand);
        pagarmeCard.updateInstallmentsElement(e);
        cardFields.forEach( function (field) {
            wrapper.find(field)[method]();
            wrapper.find(field).find('input').val('');
        });
    },
    addEventListener: function (paymentTarget) {
        $(paymentTarget + ' ' + cardSaveTarget).on('change', function (e) {
            pagarmeCheckoutWallet.onChangeCard(e);
        });
    },
    start: function (paymentTarget) {
        this.addEventListener(paymentTarget);
    }
}
