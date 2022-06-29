import Compatibility from '../Compatibility';

declare let wc_braintree_googlepay_params: any;

class BraintreeForWooCommerce extends Compatibility {
    constructor() {
        super( 'BraintreeForWooCommerce' );
    }

    load(): void {
        if ( typeof wc_braintree_googlepay_params === 'undefined' ) {
            return;
        }

        wc_braintree_googlepay_params.button_options.buttonSizeMode = 'fill';

        // Fix Braintree for WooCommerce buttons that don't initiate
        jQuery( window ).on( 'load', () => {
            jQuery( '#cfw-payment-request-buttons' ).find( '.wc-braintree-banner-gateway' ).each( ( index: number, element: any ) => {
                if ( element && jQuery( element ).children().length === 0 ) {
                    const trimmedHTMLContents = jQuery( element ).html().toString().trim();
                    jQuery( element ).html( trimmedHTMLContents );
                }
            } );
        } );
    }
}

export default BraintreeForWooCommerce;
