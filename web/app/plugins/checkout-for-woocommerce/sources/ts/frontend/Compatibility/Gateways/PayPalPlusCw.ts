import Main          from '../../Main';
import Compatibility from '../Compatibility';

class PayPalPlusCw extends Compatibility {
    /**
     * @param {Main} main The Main object
     * @param {any} params Params for the child class to run on load
     */
    constructor() {
        super( 'PayPalPlusCw' );
    }

    load(): void {
        jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
            Main.instance.updateCheckoutService.queueUpdateCheckout();
        } );
    }
}

export default PayPalPlusCw;
