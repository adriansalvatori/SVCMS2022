import Alert         from '../../Components/Alert';
import Main          from '../../Main';
import AlertService  from '../../Services/AlertService';
import TabService    from '../../Services/TabService';
import Compatibility from '../Compatibility';

class MyShipper extends Compatibility {
    constructor() {
        super( 'MyShipper' );
    }

    load( params ): void {
        const easyTabsWrap: any = Main.instance.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:after', () => {
            if ( Main.instance.tabService.getCurrentTab().attr( 'id' ) === TabService.shippingMethodTabId ) {
                Main.instance.updateCheckoutService.triggerUpdateCheckout();
            }
        } );

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                const selectedShippingMethod = jQuery( "input[name='shipping_method[0]']:checked" );
                const shippingNumber = jQuery( 'input.shipper_number' ).first();

                if ( selectedShippingMethod.length && selectedShippingMethod.val().toString().indexOf( 'use_my_shipper' ) !== -1 ) {
                    if ( shippingNumber.length === 0 || shippingNumber.val() === '' ) {
                        // Prevent removing alert on next update checkout
                        AlertService.preserveAlerts = true;

                        const alert: Alert = new Alert( 'error', params.notice, 'cfw-alert-error', true );
                        AlertService.queueAlert( alert );
                        AlertService.showAlerts();

                        event.stopImmediatePropagation();

                        return false;
                    }
                }
            }

            return true;
        } );
    }
}

export default MyShipper;
