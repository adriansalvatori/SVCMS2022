import Compatibility from '../Compatibility';

class WooSquarePro extends Compatibility {
    constructor() {
        super( 'WooSquarePro' );
    }

    load(): void {
        jQuery( window ).on( 'payment_method_selected updated_checkout', () => {
            const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();

            if ( sameAsShipping === 'same_as_shipping' ) {
                jQuery( '#billing_postcode' ).val( jQuery( '#shipping_postcode' ).val() );
            }

            if ( typeof ( <any>jQuery ).WooSquare_payments !== 'undefined' ) {
                ( <any>jQuery ).WooSquare_payments.loadForm();
            }
        } );

        jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
            if ( typeof ( <any>jQuery ).WooSquare_payments !== 'undefined' ) {
                ( <any>jQuery ).WooSquare_payments.loadForm();
            }
        } );
    }
}

export default WooSquarePro;
