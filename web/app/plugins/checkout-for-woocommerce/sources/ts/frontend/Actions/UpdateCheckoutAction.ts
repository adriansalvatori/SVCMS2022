import Alert                 from '../Components/Alert';
import Main                  from '../Main';
import AlertService          from '../Services/AlertService';
import DataService           from '../Services/DataService';
import LoggingService        from '../Services/LoggingService';
import TabService            from '../Services/TabService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';
import Action                from './Action';

class UpdateCheckoutAction extends Action {
    private static _underlyingRequest: any = null;

    private static _fragments: any = [];

    private blockUISelector: string;

    /**
     * @param fields
     */
    constructor() {
        super( 'update_checkout' );

        this.blockUISelector = '#cfw-billing-methods, .cfw-review-pane, #cfw-cart-summary, #cfw-place-order, #cfw-payment-request-buttons, #cfw-mobile-total, .cfw-order-bumps, #cfw-shipping-methods';
    }

    public load( data: any ): void {
        this.blockUI();

        if ( UpdateCheckoutAction.underlyingRequest !== null ) {
            UpdateCheckoutAction.underlyingRequest.abort();
        }

        const currentTime = new Date();
        const n = currentTime.getTime();
        const url = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', this.id );

        UpdateCheckoutAction.underlyingRequest = jQuery.ajax( {
            type: 'POST',
            url: `${url}&nocache=${n}`,
            data,
            success: this.response.bind( this ),
            error: this.error.bind( this ),
            complete: this.complete.bind( this ),
            dataType: 'json',
            cache: false,
        } );
    }

    public blockUI(): void {
        jQuery( this.blockUISelector ).not( '.cfw-blocked' ).block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0,
            },
        } ).addClass( 'cfw-blocked' );
    }

    public unblockUI(): void {
        jQuery( this.blockUISelector ).unblock().removeClass( 'cfw-blocked' );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            this.error( false, 'Invalid response', 'Server did not return proper JSON response.' );
            return;
        }

        if ( resp.redirect !== false ) {
            window.location = resp.redirect;
        }

        /**
         * Save payment details to a temporary object
         */
        const paymentBoxInputsSelector = '.payment_box :input';
        const paymentBoxInputs =  jQuery( paymentBoxInputsSelector );
        const paymentDetails = {};
        paymentBoxInputs.each( function () {
            const ID = jQuery( this ).attr( 'id' );

            if ( ID ) {
                if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                    paymentDetails[ ID ] = jQuery( this ).prop( 'checked' );
                } else {
                    paymentDetails[ ID ] = jQuery( this ).val();
                }
            }
        } );

        /**
         * Update Fragments
         *
         * For our elements as well as those from other plugins
         */
        if ( resp.fragments ) {
            jQuery.each( resp.fragments, ( key: any, value ) => {
                // eslint-disable-next-line max-len
                if ( !Object.keys( UpdateCheckoutAction._fragments ).length || UpdateCheckoutAction.cleanseFragments( UpdateCheckoutAction._fragments[ key ] ) !== UpdateCheckoutAction.cleanseFragments( value ) ) {
                    /**
                     * Make sure value is truthy
                     *
                     * Because if it's false (say for Amazon Pay) we don't want to replace anything
                     */
                    if ( typeof value === 'string' ) {
                        jQuery( key ).replaceWith( value );
                    }
                }
            } );

            UpdateCheckoutAction._fragments = resp.fragments;
        }

        if ( !resp.show_shipping_tab ) {
            jQuery( 'body' ).addClass( 'cfw-hide-shipping' );

            // In case the current tab gets hidden
            if ( Main.instance.tabService.getCurrentTab().is( ':hidden' ) ) {
                TabService.go( Main.instance.tabService.getCurrentTab().prev().attr( 'id' ) );
            }
        } else {
            jQuery( 'body' ).removeClass( 'cfw-hide-shipping' );
        }

        jQuery( '.cfw-continue-to-payment-btn' ).not( '.cfw-smartystreets-button' ).toggle( resp.has_valid_shipping_method );

        /**
         * Fill in the payment details if possible without overwriting data if set.
         */
        if ( !jQuery.isEmptyObject( paymentDetails ) ) {
            jQuery( paymentBoxInputsSelector ).each( function () {
                const ID = jQuery( this ).attr( 'id' );
                const val = jQuery( this ).val();

                if ( ID ) {
                    if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                        jQuery( this ).prop( 'checked', paymentDetails[ ID ] ).trigger( 'change' );
                    } else if ( val !== null && val.toString().length === 0 ) {
                        jQuery( this ).val( paymentDetails[ ID ] ).trigger( 'change' );
                    }
                }
            } );
        }

        const alerts = [];

        if ( resp.notices.success ) {
            Object.keys( resp.notices.success ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'success', resp.notices.success[ key ], 'cfw-alert-success' ) );
            } );
        }

        if ( resp.notices.notice ) {
            Object.keys( resp.notices.notice ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'notice', resp.notices.notice[ key ], 'cfw-alert-info' ) );
            } );
        }

        if ( resp.notices.error ) {
            Object.keys( resp.notices.error ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'error', resp.notices.error[ key ], 'cfw-alert-error' ) );
            } );
        }

        alerts.forEach( ( alert ) => { AlertService.queueAlert( alert ); } );

        // Fire finishing events
        jQuery( document.body ).trigger( 'cfw_pre_updated_checkout', [ resp ] );
        LoggingService.logEvent( 'Fired cfw_pre_updated_checkout event.' );

        UpdateCheckoutService.triggerUpdatedCheckout( resp );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        if ( textStatus !== 'abort' ) {
            const alert = new Alert( 'error', DataService.getMessage( 'update_checkout_error' ), 'cfw-alert-error' );
            AlertService.queueAlert( alert );
        }

        super.error( xhr, textStatus, errorThrown );
    }

    public complete( xhr: any, textStatus: string ): void {
        // We are treating an aborted request as one that will always have a subsequent request
        if ( textStatus !== 'abort' ) {
            this.unblockUI();
        }
    }

    /**
     * Cleanses our beautiful fragments of evil dirty bad stuff
     *
     * @param value
     * @returns {string}
     */
    static cleanseFragments( value: string ) {
        if ( typeof value !== 'string' ) {
            return value;
        }

        return value.replace( /checked='checked' data-order_button_text/g, 'data-order_button_text' )
            .replace( /reveal-content" style="display:none;">/g, 'reveal-content">' )
            .replace( /cfw-radio-reveal-li cfw-active/g, 'cfw-radio-reveal-li' )
            .replace( /cfw-radio-reveal-li ">/g, 'cfw-radio-reveal-li">' )
            .replace( /cfw-radio-reveal-content" >/g, 'cfw-radio-reveal-content">' )
            .replace( / /g, '' );
    }

    /**
     * @returns {any}
     */
    static get underlyingRequest(): any {
        return this._underlyingRequest;
    }

    /**
     * @param value
     */
    static set underlyingRequest( value: any ) {
        this._underlyingRequest = value;
    }
}

export default UpdateCheckoutAction;
