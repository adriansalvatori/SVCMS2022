import Alert                   from '../Components/Alert';
import AlertService            from '../Services/AlertService';
import Action                  from './Action';

class LostPasswordAction extends Action {
    /**
     */
    constructor() {
        super( 'cfw_lost_password' );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        jQuery( '#cfw-lp-alert-placeholder' ).empty();
        jQuery( '#cfw_lost_password_form #user_login' ).val( '' );

        const alerts = [];

        // Errors
        if ( resp.result ) {
            const alert: Alert = new Alert( 'success', resp.message, 'cfw-alert-success cfw-coupon-alert', true );
            alerts.push( alert );
        } else {
            const alert: Alert = new Alert( 'error', resp.message, 'cfw-alert-error cfw-coupon-alert', true );
            alerts.push( alert );
        }

        alerts.forEach( ( alert ) => {
            const alertElement = AlertService.getOrBuildAlert( alert.type, alert.message, alert.cssClass );

            alertElement.toggleClass( 'cfw-alert-temporary', alert.temporary );
            alertElement.appendTo( '#cfw-lp-alert-placeholder' );
        } );

        if ( resp.result ) {
            jQuery( '#cfw_lost_password_form > * ' ).not( '#cfw-lp-alert-placeholder' ).hide();
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        const alert: Alert = new Alert( 'error', `An error occurred during login. Error: ${errorThrown} (${textStatus})`, 'cfw-alert-error' );
        AlertService.queueAlert( alert );

        super.error( xhr, textStatus, errorThrown );
    }
}

export default LostPasswordAction;
