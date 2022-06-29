import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooCommerceGermanized extends Compatibility {
    constructor() {
        super( 'WooCommerceGermanized' );
    }

    load(): void {
        jQuery( window ).on( 'load', () => {
            jQuery( document ).off( 'change', '.payment_methods input[name="payment_method"]' );
        } );
    }
}

export default WooCommerceGermanized;
