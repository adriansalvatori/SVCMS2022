import Compatibility from '../Compatibility';

// eslint-disable-next-line camelcase
declare let wc_eu_vat_params: any;

class EUVatNumber extends Compatibility {
    constructor() {
        super( 'EUVatNumber' );
    }

    load(): void {
        // If shipping country or ship to different address value changes, we need to catch it
        jQuery( 'form.checkout' ).on( 'change', 'select#shipping_country, input[name="bill_to_different_address"]', () => {
            const country = jQuery( 'select#shipping_country' ).val();
            // eslint-disable-next-line camelcase
            const checkCountries = wc_eu_vat_params.eu_countries;
            const same_as_shipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();

            if ( country && jQuery.inArray( country, checkCountries ) >= 0 && same_as_shipping === 'same_as_shipping' ) {
                // If shipping country is in EU and same as shipping address is checked, show vat number
                jQuery( '#woocommerce_eu_vat_number' ).fadeIn();
            } else if ( country && jQuery.inArray( country, checkCountries ) === -1 && same_as_shipping === 'same_as_shipping' ) {
                // If shipping country is not in EU and same as shipping address is checked, hide vat number
                jQuery( '#woocommerce_eu_vat_number' ).fadeOut();
            } else {
                // Otherwise, trigger a change on the billing country so
                // that EU Vat Number's native JS will run
                jQuery( 'select#billing_country' ).trigger( 'change' );
            }
        } );

        // Make sure that on refresh, we trigger a change on shipping
        // country so that the field renders in the right state
        jQuery( window ).on( 'load', () => {
            jQuery( 'select#shipping_country' ).trigger( 'change' );

            // For latest version of EU VAT Number, move the VAT number field below the billing address
            // so that it's usable when billing is same as shipping
            const euVatRow = jQuery( '#woocommerce_eu_vat_number_field' ).parent( '.cfw-input-wrap-row' );
            euVatRow.insertAfter( '#cfw-shipping-same-billing' );
        } );
    }
}

export default EUVatNumber;
