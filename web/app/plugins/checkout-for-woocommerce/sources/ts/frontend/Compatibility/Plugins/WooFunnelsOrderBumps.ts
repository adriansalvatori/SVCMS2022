import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooFunnelsOrderBumps extends Compatibility {
    constructor() {
        super( 'WooFunnelsOrderBumps' );
    }

    load(): void {
        /**
         * When 'wfob_cart_goes_empty' fires the immediate next 'wfob_bump_trigger' shouldn't trigger a checkout update.
         * The reassignment of updateCheckout to setUpdateCheckout ensures that the next call to updateCheckout does essentially nothing except reset the value for updateCheckout.
         * This will skip the updateCheckout once and only once.
         */
        let handler: any;

        const resetHandler = () => {
            handler = () => Main.instance.updateCheckoutService.queueUpdateCheckout();
        };

        resetHandler();

        jQuery( document.body ).on( 'wfob_bump_trigger', () => {
            handler();
        } );

        jQuery( document.body ).on( 'wfob_cart_goes_empty', () => {
            handler = resetHandler;
        } );
    }
}

export default WooFunnelsOrderBumps;
