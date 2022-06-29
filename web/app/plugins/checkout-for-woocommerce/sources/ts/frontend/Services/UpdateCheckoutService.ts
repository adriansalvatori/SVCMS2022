import UpdateCheckoutAction from '../Actions/UpdateCheckoutAction';
import Main                 from '../Main';
import DataService          from './DataService';
import LoggingService       from './LoggingService';
import TabService           from './TabService';

class UpdateCheckoutService {
    private _updateCheckoutTimer: any;

    constructor() {
        this.setUpdateCheckoutTriggers();
    }

    queueUpdateCheckout( e?, args? ): true {
        const keyCode = e?.keyCode ?? e?.which;

        if ( keyCode === 9 ) {
            return true;
        }

        this.resetUpdateCheckoutTimer();
        jQuery( document.body ).trigger( 'cfw_queue_update_checkout' );
        LoggingService.logEvent( 'Fired cfw_queue_update_checkout event.' );

        this.updateCheckoutTimer = window.setTimeout( this.maybeUpdateCheckout.bind( this ), 1000, args );

        return true;
    }

    /**
     * All update_checkout triggers should happen here
     *
     * Exceptions would be edge cases involving TS compat classes
     */
    setUpdateCheckoutTriggers(): void {
        // WooCommerce Core listens for both update and update_checkout
        jQuery( document.body ).on( 'update update_checkout', ( e, args ) => this.maybeUpdateCheckout( args ) );

        const { checkoutForm } = DataService;

        const selectors = [
            '[name="bill_to_different_address"]',
            '.update_totals_on_change select',
            '.update_totals_on_change input[type="radio"]',
            '.update_totals_on_change input[type="checkbox"]',
            '.address-field select',
            '.address-field input.input-text',
            '.update_totals_on_change input.input-text',
            '#wc_checkout_add_ons :input',
            'input.update_totals_on_change',
        ];

        checkoutForm.on( 'change', selectors.join( ', ' ), this.maybeUpdateCheckout.bind( this ) );

        checkoutForm.on( 'change', 'select.shipping_method, input[name^="shipping_method"]', () => {
            jQuery( document.body ).trigger( 'cfw-shipping-method-changed' );
            LoggingService.logEvent( 'Fired cfw-shipping-method-changed event.' );
            this.maybeUpdateCheckout();
        } );

        checkoutForm.on( 'change', '#billing_email', () => {
            if ( DataService.getSetting( 'enable_one_page_checkout' ) ) {
                return;
            }

            this.maybeUpdateCheckout();
        } ); // for the shipping address preview

        // The below line matches the WooCommerce core update_checkout trigger
        // But aren't using it because it's overly aggressive and causes errors that pull the customer out of context for slow typists
        // checkoutForm.on( 'keydown', '.address-field input.input-text, .update_totals_on_change input.input-text', this.queueUpdateCheckout.bind( this ) );

        jQuery( document.body ).on( 'init_checkout', this.maybeUpdateCheckout.bind( this ) );

        /**
         * Special Case: Order Review tab
         *
         * This clears any errors and ensures that information is up to date
         */
        jQuery( document.body ).on( 'cfw-after-tab-change', () => {
            const currentTab = Main.instance.tabService.getCurrentTab();

            if ( currentTab.attr( 'id' ) === TabService.orderReviewTabId ) {
                this.queueUpdateCheckout();
            }
        } );

        /**
         * Special case: User logged in and we need to make sure the name updates
         */
        if ( DataService.getSetting( 'user_logged_in' ) ) {
            jQuery( document.body ).on( 'cfw-after-tab-change', () => {
                const currentTab = Main.instance.tabService.getCurrentTab();

                if ( currentTab.attr( 'id' ) === TabService.shippingMethodTabId ) {
                    this.queueUpdateCheckout();
                }
            } );
        }
    }

    /**
     * reset the update checkout timer (stop iteration)
     */
    resetUpdateCheckoutTimer(): void {
        clearTimeout( this.updateCheckoutTimer );
    }

    maybeUpdateCheckout( args?: any ): void {
        this.resetUpdateCheckoutTimer();

        this.updateCheckoutTimer =  window.setTimeout( this.triggerUpdateCheckout.bind( this ), 5, args );
    }

    /**
     * Call update_checkout
     *
     * This should be the ONLY place we call this ourselves
     */
    triggerUpdateCheckout(): void {
        if ( DataService.getSetting( 'is_checkout_pay_page' ) ) {
            return;
        }

        const args = {
            update_shipping_method: true,
        };

        new UpdateCheckoutAction().load( UpdateCheckoutService.getData( args ) );
    }

    /**
     * Call updated_checkout
     *
     * This should be the ONLY place we call this ourselves
     */
    static triggerUpdatedCheckout( data? ): void {
        if ( typeof data === 'undefined' ) {
            // If this is running in the dark, we need
            // to shim in fragments because some plugins
            // ( like WooCommerce Smart Coupons ) expect it
            // eslint-disable-next-line no-param-reassign
            data = { fragments: {} };
        }

        jQuery( document.body ).trigger( 'updated_checkout', [ data ] );
        LoggingService.logEvent( 'Fired updated_checkout event.' );
    }

    /**
     * @param args
     */
    static getData( args? ): Record<string, unknown> {
        /* eslint-disable camelcase */
        const { checkoutForm } = DataService;
        const billToDifferentAddress = jQuery( '[name="bill_to_different_address"]:checked' ).val() as string;
        const requiredShippingAddressFieldInputs         = checkoutForm.find( '.woocommerce-shipping-fields .address-field.validate-required:visible' );
        let has_full_address  = true;

        const billing_email = jQuery( '#billing_email' ).val();
        const s_company     = jQuery( '#shipping_company' ).val();
        const s_country     = jQuery( '#shipping_country' ).val();
        const s_state       = jQuery( '#shipping_state' ).val();
        const s_postcode    = jQuery( ':input#shipping_postcode' ).val();
        const s_city        = jQuery( '#shipping_city' ).val();
        const s_address     = jQuery( ':input#shipping_address_1' ).val();
        const s_address_2   = jQuery( ':input#shipping_address_2' ).val();
        let company         = s_company;
        let country         = s_country;
        let state           = s_state;
        let postcode        = s_postcode;
        let city            = s_city;
        let address         = s_address;
        let address_2       = s_address_2;

        if ( billToDifferentAddress !== 'same_as_shipping' ) {
            company   = jQuery( '#billing_company' ).val();
            country   = jQuery( '#billing_country' ).val();
            state     = jQuery( '#billing_state' ).val();
            postcode  = jQuery( ':input#billing_postcode' ).val();
            city      = jQuery( '#billing_city' ).val();
            address   = jQuery( ':input#billing_address_1' ).val();
            address_2 = jQuery( ':input#billing_address_2' ).val();
        }

        if ( requiredShippingAddressFieldInputs.length ) {
            // eslint-disable-next-line func-names
            requiredShippingAddressFieldInputs.each( function () {
                if ( jQuery( this ).find( ':input' ).val() === '' ) {
                    has_full_address = false;
                }
            } );
        }

        const data = {
            security: DataService.getCheckoutParam( 'update_order_review_nonce' ),
            payment_method: checkoutForm.find( 'input[name="payment_method"]:checked' ).val(),
            billing_email, // has to be here or the field isn't accessible through WC()->checkout()->get_value()
            company,
            country,
            state,
            postcode,
            city,
            address,
            address_2,
            s_company,
            s_country,
            s_state,
            s_postcode,
            s_city,
            s_address,
            s_address_2,
            has_full_address,
            bill_to_different_address: billToDifferentAddress,
            post_data: checkoutForm.serialize(),
            shipping_method: undefined,
        };

        if ( typeof args !== 'undefined' && typeof args.update_shipping_method !== 'undefined' && args.update_shipping_method !== false ) {
            const shipping_methods = {};

            // eslint-disable-next-line max-len,func-names
            jQuery( 'select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]' ).each( function () {
                shipping_methods[ jQuery( this ).data( 'index' ) ] = jQuery( this ).val();
            } );

            data.shipping_method = shipping_methods;
        }

        return data;
        /* eslint-enable camelcase */
    }

    get updateCheckoutTimer(): any {
        return this._updateCheckoutTimer;
    }

    set updateCheckoutTimer( value: any ) {
        this._updateCheckoutTimer = value;
    }
}

export default UpdateCheckoutService;
