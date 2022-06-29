import Main          from '../../Main';
import Compatibility from '../Compatibility';

declare let pvkw: any;

class PortugalVaspKios extends Compatibility {
    constructor() {
        super( 'PortugalVaspKios' );
    }

    load(): void {
        jQuery( document.body ).on( 'updated_checkout', () => {
            jQuery( ( $ ) => {
                $( '#pvkw' ).hide();
                $( '#pvkw_point_active' ).val( '0' );

                let country: any;

                // Country - we only do this for Portugal
                if ( $( '#ship-to-different-address' ).find( 'input' ).is( ':checked' ) ) {
                    country = $( '#shipping_country' ).val();
                } else {
                    country = $( '#billing_country' ).val();
                }
                if ( country === 'PT' ) {
                    // checkout.js : 271
                    const shippingMethods = {};
                    $( 'select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]' ).each( function () {
                        shippingMethods[ $( this ).data( 'index' ) ] = $( this ).val();
                    } );
                    // Only one shipping method chosen?
                    if ( Object.keys( shippingMethods ).length == 1 ) {
                        const shippingMethod = $.trim( shippingMethods[ 0 ] );
                        if ( $.inArray( shippingMethod, pvkw.shipping_methods ) >= 0 ) {
                            $( '#pvkw' ).show();
                            $( '#pvkw_point_active' ).val( '1' );
                            if ( $().select2 ) {
                                $( '#pvkw_point' ).select2();
                            }
                        }
                    }
                }
            } );
        } );
    }
}

export default PortugalVaspKios;
