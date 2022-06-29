import Alert                from '../Components/Alert';
import Main                 from '../Main';
import AlertService         from '../Services/AlertService';
import DataService          from '../Services/DataService';
import LoggingService       from '../Services/LoggingService';
import Action               from './Action';

class ApplyCouponAction extends Action {
    private code: string;

    /**
     * @param {string} code
     */
    constructor( code: string ) {
        super( 'apply_coupon' );

        this.code = code;
    }

    load( data: any ): void {
        const currentTime = new Date();
        const n = currentTime.getTime();
        const url = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', this.id );

        this.code = data.coupon_code;

        jQuery.ajax( {
            type: 'POST',
            url: `${url}&nocache=${n}`,
            data,
            success: this.response.bind( this ),
            error: this.error.bind( this ),
            dataType: 'html',
            cache: false,
        } );
    }

    /**
     *
     * @param resp
     */
    public response( resp: string ): void {
        let success = false;

        // Wrapping the response in a <div /> is required for correct parsing
        const messages = jQuery( jQuery.parseHTML( `<div>${resp}</div>` ) );

        // Errors
        const woocommerceErrorMessages = messages.find( '.woocommerce-error li' ).length ? messages.find( '.woocommerce-error li' ) : messages.find( '.woocommerce-error' );

        jQuery.each( woocommerceErrorMessages, ( i, el ) => {
            const alert: Alert = new Alert( 'error', jQuery( el ).html().trim(), 'cfw-alert-error cfw-coupon-alert', true );
            AlertService.queueAlert( alert );
        } );

        // Info
        const wooCommerceInfoMessages = messages.find( '.woocommerce-info li' ).length ? messages.find( '.woocommerce-info li' ) : messages.find( '.woocommerce-info' );

        jQuery.each( wooCommerceInfoMessages, ( i, el ) => {
            const alert: Alert = new Alert( 'notice', jQuery( el ).html().trim(), 'cfw-alert-info cfw-coupon-alert', true );
            AlertService.queueAlert( alert );
        } );

        // Messages
        const wooCommerceMessages = messages.find( '.woocommerce-message li' ).length ? messages.find( '.woocommerce-message li' ) : messages.find( '.woocommerce-message' );

        jQuery.each( wooCommerceMessages, ( i, el ) => {
            success = true;

            const alert: Alert = new Alert( 'success', jQuery( el ).html().trim(), 'cfw-alert-success cfw-coupon-alert', true );
            AlertService.queueAlert( alert );
        } );

        if ( success ) {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-success' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-success event.' );
        } else {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-failure' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-failure event.' );
        }

        jQuery( document.body ).trigger( 'cfw-apply-coupon-complete' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-complete event.' );

        AlertService.preserveAlerts = true;

        // The following simulates exactly what happens at the end a coupon request through native Woo
        jQuery( document.body ).trigger( 'applied_coupon_in_checkout', [ this.code ] );
        Main.instance.updateCheckoutService.queueUpdateCheckout( {}, { update_shipping_method: false } );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        jQuery( document.body ).trigger( 'cfw-apply-coupon-error' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-error event.' );

        const alert: Alert = new Alert( 'error', `Failed to apply coupon. Error: ${errorThrown} (${textStatus})`, 'cfw-alert-error' );
        AlertService.queueAlert( alert );

        super.error( xhr, textStatus, errorThrown );
    }
}

export default ApplyCouponAction;
