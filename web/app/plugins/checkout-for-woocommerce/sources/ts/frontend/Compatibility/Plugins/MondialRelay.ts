import Alert         from '../../Components/Alert';
import Main          from '../../Main';
import AlertService  from '../../Services/AlertService';
import TabService    from '../../Services/TabService';
import Compatibility from '../Compatibility';

declare let mrwpPluginSettings: any;
declare function mrwp_prepare_shipping() : boolean;
declare function mrwpParcelPickerInit() : void;
declare function mrwpShippingCode( shippingIds: string, selectedShipping: string ) : string;
declare function mrwpNeedsParcelPicker( option: boolean ) : boolean;

class MondialRelay extends Compatibility {
    constructor() {
        super( 'MondialRelay' );
    }

    load(): void {
        jQuery( '#cfw-shipping-action' ).hide();

        jQuery( document.body ).on( 'updated_checkout', () => {
            jQuery( '#cfw-shipping-action' ).show();
        } );

        const easyTabsWrap: any = Main.instance.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:before', ( event, clicked, target ) => {
            if ( jQuery( target ).attr( 'id' ) === TabService.paymentMethodTabId ) {
                if ( jQuery( '#mrwp_parcel_shop_mandatory' ).val() === 'Yes' ) {
                    if ( jQuery( '#mrwp_parcel_shop_id' ).val() === '' ) {
                        // Prevent removing alert on next update checkout
                        AlertService.preserveAlerts = true;

                        const alert: Alert = new Alert( 'error', 'Vous n\'avez pas encore choisi de Point Relais.', 'cfw-alert-error', true );
                        AlertService.queueAlert( alert );

                        AlertService.showAlerts();

                        event.stopImmediatePropagation();

                        return false;
                    }
                }
            }
        } );
    }
}

export default MondialRelay;
