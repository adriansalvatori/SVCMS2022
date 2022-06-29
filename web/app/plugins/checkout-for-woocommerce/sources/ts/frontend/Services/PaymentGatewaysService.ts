import cfwUpdatePaymentMethod from '../../functions/cfwUpdatePaymentMethod';
import DataService            from './DataService';
import LoggingService         from './LoggingService';
import ClickEvent = JQuery.ClickEvent;

class PaymentGatewaysService {
    private _selectedGateway: any = false;

    constructor() {
        // This simulates what WooCommerce core does
        jQuery( document.body ).on( 'click', 'input[name="payment_method"]', ( e: ClickEvent ) => {
            this.paymentGatewayChangeHandler( e );
        } );

        jQuery( document.body ).on( 'cfw_pre_updated_checkout', () => {
            this.initSelectedPaymentGateway();
        } );

        jQuery( document.body ).on( 'cfw-payment-tab-loaded', () => {
            // Fix Stripe rendering issue
            setTimeout( () => {
                jQuery( '.wc_payment_method.cfw-active .payment_box' ).hide().show( 0 );
            }, 100 );
        } );

        this.initSelectedPaymentGateway();
    }

    /**
     * Find the selected payment gateway and trigger a click
     *
     * Some gateways look for a click action to init themselves properly
     */
    initSelectedPaymentGateway(): void {
        const paymentMethods = jQuery( '.woocommerce-checkout input[name="payment_method"]' );

        if ( paymentMethods.length === 1 ) {
            paymentMethods.hide();
        }

        // If there was a previously selected method, check that one.
        if ( this._selectedGateway !== false ) {
            jQuery( `#${this._selectedGateway}` ).prop( 'checked', true );
        }

        // If there are none selected, select the first.
        if ( paymentMethods.filter( ':checked' ).length === 0 ) {
            paymentMethods.eq( 0 ).prop( 'checked', true );
        }

        const checkedPaymentMethodId = paymentMethods.filter( ':checked' ).eq( 0 ).prop( 'id' );

        if ( paymentMethods.length > 1 ) {
            // Hide open descriptions.
            jQuery( `div.payment_box:not(".${checkedPaymentMethodId}")` ).filter( ':visible' ).slideUp( 0 );
        }

        paymentMethods.filter( ':checked' ).eq( 0 ).trigger( 'click' );
    }

    paymentGatewayChangeHandler( e: ClickEvent ): void {
        const selectedPaymentMethod = jQuery( '.woocommerce-checkout input[name="payment_method"]:checked' );

        if ( !selectedPaymentMethod.length ) {
            return;
        }

        const placeOrderButton = jQuery( '#place_order' );

        if ( selectedPaymentMethod.data( 'order_button_text' ) ) {
            placeOrderButton.text( selectedPaymentMethod.data( 'order_button_text' ) );
        } else {
            placeOrderButton.text( placeOrderButton.data( 'value' ) );
        }

        const paymentMethod = selectedPaymentMethod.val().toString();

        // Humans only please
        if ( typeof e.originalEvent !== 'undefined' ) {
            cfwUpdatePaymentMethod( paymentMethod );
        }

        const currentSelectedPaymentMethod = selectedPaymentMethod.attr( 'id' );

        if ( currentSelectedPaymentMethod !== this._selectedGateway ) {
            jQuery( document.body ).trigger( 'payment_method_selected' );
            LoggingService.logEvent( 'Fired payment_method_selected event.' );
        }

        this._selectedGateway = currentSelectedPaymentMethod;
    }
}

export default PaymentGatewaysService;
