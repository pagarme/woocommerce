/* globals wc_pagarme_checkout */
$ = jQuery;
const cardSaveTarget = 'select[data-element="choose-credit-card"]';
const cardFields = [
    '[data-pagarmecheckout-element="fields-cc-data"]',
    '[data-element="save-cc-check"]',
    '[data-element="enable-multicustomers-check"]'
];
let pagarmeWallet = {
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
        let method  = e.currentTarget.value.trim() ? 'slideUp': 'slideDown';
        let brand = select.find('option:selected').data('brand');
        let brandInput = wrapper.find(pagarmeCard.getBrandTarget());
        brandInput.val(brand);
        cardFields.forEach( function (field) {
            wrapper.find(field)[method]();
        });
    },
    addEventListener: function () {
        $(cardSaveTarget).on('change', function (e) {
            pagarmeWallet.onChangeCard(e);
        });
    },
    start: function () {
        if (this.isStarted()) {
            return;
        }
        this.addEventListener();
    },
}
