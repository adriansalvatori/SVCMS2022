import Alert        from '../frontend/Components/Alert';
import AlertService from '../frontend/Services/AlertService';
import DataService  from '../frontend/Services/DataService';

export default function cfwValidateShippingTab(): Promise<any> {
    return new Promise( ( resolve, reject ) => {
        jQuery( '#cfw-shipping-method .validate-required:visible' ).each( ( i, el ) => {
            const container = jQuery( el );
            const field = container.find( ':input' ).not( '[data-parsley-group]' );

            if ( field.val() !== '' ) {
                return; // continue
            }

            // If field doesn't have label, look for TH
            const label = container.find( 'label' ).text() || container.closest( 'td' ).siblings( 'th' ).text();
            const template = DataService.getMessage( 'generic_field_validation_error_message' );
            const message = template.replace( '%s', label );

            AlertService.queueAlert( new Alert( 'error', message, 'cfw-alert-error' ) );
            AlertService.showAlerts();

            reject( new Error( 'CheckoutWC: A non-parsley required field was detected to be invalid.' ) );
        } );

        resolve();
    } );
}
