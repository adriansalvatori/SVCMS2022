import FormField     from '../../Components/FormField';
import Compatibility from '../Compatibility';

class PostNL extends Compatibility {
    constructor() {
        super( 'PostNL' );
    }

    load(): void {
        jQuery( document.body ).on( 'updated_checkout', () => {
            // Shipping address
            const shipping_street_name = jQuery( '#shipping_street_name' );
            const shipping_house_number = jQuery( '#shipping_house_number' );
            const shipping_house_number_suffix = jQuery( '#shipping_house_number_suffix' );
            const shipping_city = jQuery( '#shipping_city' );
            let shipping_address_1 = '';

            // Fix float labels
            if ( shipping_street_name.val() ) {
                shipping_street_name.parent().addClass( FormField.floatClass );
            }

            if ( shipping_city.val() ) {
                shipping_city.parent().addClass( FormField.floatClass );
            }

            // Set address 1
            if ( shipping_street_name.val() && shipping_house_number.val() ) {
                shipping_address_1 = `${shipping_street_name.val()} ${shipping_house_number.val()}`;
            }

            if ( shipping_house_number_suffix.val() && shipping_address_1 ) {
                shipping_address_1 = `${shipping_address_1}-${shipping_house_number_suffix.val()}`;
            }

            if ( shipping_address_1 ) {
                jQuery( '#shipping_address_1' ).val( shipping_address_1 );
            }

            // Billing address
            const billing_street_name = jQuery( '#billing_street_name' );
            const billing_house_number = jQuery( '#billing_house_number' );
            const billing_house_number_suffix = jQuery( '#billing_house_number_suffix' );
            const billing_city = jQuery( '#billing_city' );
            let billing_address_1 = '';

            // Fix float labels
            if ( billing_street_name.val() ) {
                billing_street_name.parent().addClass( FormField.floatClass );
            }

            if ( billing_city.val() ) {
                billing_city.parent().addClass( FormField.floatClass );
            }

            // Set address 1
            if ( billing_street_name.val() && billing_house_number.val() ) {
                billing_address_1 = `${billing_street_name.val()} ${billing_house_number.val()}`;
            }

            if ( billing_house_number_suffix.val() && billing_address_1 ) {
                billing_address_1 = `${billing_address_1}-${billing_house_number_suffix.val()}`;
            }

            if ( billing_address_1 ) {
                jQuery( '#billing_address_1' ).val( billing_address_1 );
            }
        } );

        jQuery( window ).on( 'load', () => {
            // Hide empty containers from WC Postcode Checker NL moving fields around
            jQuery( '.row:not(:has(*))' ).hide();

            // Add spacing due to moving fields around
            jQuery( '.col-lg-12' ).filter( ( index, element ) => jQuery( element ).next( '.cfw-column-12' ).length !== 0 ).css( 'margin-bottom', '12.5px' );
        } );
    }
}

export default PostNL;
