import TabService    from '../../Services/TabService';
import Compatibility from '../Compatibility';

class CO2OK extends Compatibility {
    constructor() {
        super( 'CO2OK' );
    }

    load(): void {
        jQuery( document.body ).on( 'updated_checkout', () => {
            jQuery( 'a.co2ok_nolink' ).prop( 'href', `#${TabService.paymentMethodTabId}` );
        } );
    }
}

export default CO2OK;
