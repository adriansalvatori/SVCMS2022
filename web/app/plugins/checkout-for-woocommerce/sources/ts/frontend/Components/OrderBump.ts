import Main                  from '../Main';

class OrderBump {
    constructor() {
        this.setListeners();
    }

    setListeners() {
        jQuery( document.body ).on( 'change', '.cfw_order_bump_check', this.handleOfferAcceptance );
    }

    handleOfferAcceptance( event ) {
        if ( jQuery( event.currentTarget ).is( ':checked' ) ) {
            Main.instance.updateCheckoutService.queueUpdateCheckout();
        }
    }
}

export default OrderBump;
