import TabService    from '../../Services/TabService';
import Compatibility from '../Compatibility';

class Stripe extends Compatibility {
    constructor() {
        super( 'Stripe' );
    }

    load(): void {
        jQuery( document ).on( 'stripeError', this.onError );
    }

    onError(): void {
        window.location.hash = TabService.paymentMethodTabId;
    }
}

export default Stripe;
