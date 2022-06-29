import Main                 from '../../Main';
import AlertService         from '../../Services/AlertService';
import DataService          from '../../Services/DataService';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

// eslint-disable-next-line camelcase
declare let amazon_payments_advanced: any;

class AmazonPay extends Compatibility {
    protected timer: any;

    protected iterations = 0;

    constructor() {
        super( 'AmazonPay' );
    }

    load(): void {
        jQuery( document.body ).on( 'cfw_complete_order_failure', () => {
            // Amazon Pay triggers update_checkout after a failed submission
            // This prevents the generated alerts from being immediately scrubbed.
            if ( Main.instance ) {
                AlertService.preserveAlerts = true;
            }
        } );

        /**
         * If the OffAmazonPayments and amazon_payments_advanced_params exist
         * we can then check to see if there is a reference id set.
         *
         * If not we are not logged in. If there is we are logged in.
         */
        try {
            // eslint-disable-next-line max-len,camelcase
            if ( amazon_payments_advanced.checkout_session_id !== '' ) {
                jQuery( window.document ).on( 'updated_checkout', () => {
                    DataService.setRuntimeParameter( 'cfw_suppress_js_field_validation', true );

                    jQuery( '.create-account .cfw-input-wrap' ).addClass( 'cfw-label-is-floated' );
                    jQuery( 'input#createaccount' ).trigger( 'change' );
                } );

                if ( DataService.getCheckoutParam( 'option_guest_checkout' ) === 'yes' ) {
                    jQuery( document.body ).on( 'change', 'input#createaccount', () => {
                        const createAccount = jQuery( 'div.create-account' );
                        createAccount.hide();

                        if ( jQuery( this ).is( ':checked' ) ) {
                            // Ensure password is not pre-populated.
                            jQuery( '#account_password' ).val( '' ).trigger( 'change' );
                            createAccount.slideDown();
                        }
                    } );
                }

                jQuery( window ).on( 'load updated_checkout', () => {
                    this.cleanUpExtraStuff();
                } );
            }

            jQuery( '#pay_with_amazon' ).css( 'opacity', '0' );

            this.timer = setInterval( this.clearShadowRoot.bind( this ), 100 );
        } catch ( error ) {
            // eslint-disable-next-line no-console
            console.log( error );
        }
    }

    clearShadowRoot(): void {
        // Clear shadow-root from #pay_with_amazon button
        this.iterations += 1;

        if ( this.iterations > 1000 ) {
            clearInterval( this.timer );
        }
        try {
            if ( jQuery( document.body ).find( '#pay_with_amazon' ).get( 0 ).shadowRoot.innerHTML.length ) {
                jQuery( document.body ).find( '#pay_with_amazon' ).get( 0 ).shadowRoot.innerHTML = '';
                jQuery( '#pay_with_amazon' ).css( 'opacity', '1' );
                clearInterval( this.timer );
            }
        } catch ( error ) {
            // Page was probably loaded on different tab so shadowRoot was never applied
            // If so, just make it visible
            jQuery( '#pay_with_amazon' ).css( 'opacity', '1' );
            clearInterval( this.timer );
        }
    }

    cleanUpExtraStuff(): void {
        jQuery( '#payment-info-separator-wrap' ).hide();
        jQuery( '#cfw-shipping-same-billing' ).hide();
        jQuery( '#cfw-billing-methods > h3' ).hide();
        jQuery( '#cfw-customer-info-address > h3' ).hide();
        jQuery( TabService.paymentMethodTabId ).find( 'h3' ).hide();
        jQuery( '.cfw-billing-address-heading' ).hide();
        jQuery( '.cfw-billing-address-description' ).hide();
        jQuery( '.cfw-add-field' ).hide();
        jQuery( '#billing_same_as_shipping_radio' ).remove();

        const shippingPhoneFieldWrap = jQuery( '#shipping_phone_field' );

        if ( !shippingPhoneFieldWrap.hasClass( 'validate-required' ) ) {
            shippingPhoneFieldWrap.addClass( 'hidden' );
        }
    }
}

export default AmazonPay;
