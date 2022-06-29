import Main          from '../../Main';
import Compatibility from '../Compatibility';

class KlarnaPayments extends Compatibility {
    constructor() {
        super( 'KlarnaPayments' );
    }

    load(): void {
        jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
            const same_as_shipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();

            if ( same_as_shipping === 'same_as_shipping' ) {
                jQuery( '#billing_first_name' ).val( jQuery( '#shipping_first_name' ).val() );
                jQuery( '#billing_last_name' ).val( jQuery( '#shipping_last_name' ).val() );
                jQuery( '#billing_address_1' ).val( jQuery( '#shipping_address_1' ).val() );
                jQuery( '#billing_address_2' ).val( jQuery( '#shipping_address_2' ).val() );
                jQuery( '#billing_company' ).val( jQuery( '#shipping_company' ).val() );
                jQuery( '#billing_country' ).val( jQuery( '#shipping_country' ).val() );
                jQuery( '#billing_state' ).val( jQuery( '#shipping_state' ).val() );
                jQuery( '#billing_postcode' ).val( jQuery( '#shipping_postcode' ).val() );
            }

            // If this call doesn't run, Klarna won't load the iframe
            jQuery( 'input[name="payment_method"]:checked' ).trigger( 'change' );
        } );
    }
}

export default KlarnaPayments;
