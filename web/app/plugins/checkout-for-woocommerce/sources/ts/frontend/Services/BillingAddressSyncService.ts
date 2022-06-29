import LoggingService from './LoggingService';

class BillingAddressSyncService {
    constructor() {
        jQuery( window ).on( 'load', () => {
            this.listenForShippingChanges();
            this.listenForBillingChanges();
            this.listenForSameAsShippingToggle();
        } );

        // Anytime updated_checkout runs make sure we have the right billing address values
        jQuery( document.body ).on( 'updated_checkout', this.maybeSyncShippingAddressToBillingAddress.bind( this ) );
    }

    listenForShippingChanges(): void {
        jQuery( '.woocommerce-shipping-fields [name^="shipping_"]' ).on( 'change', ( event ) => {
            const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();
            const shippingField  = jQuery( event.target );
            const billingField   = jQuery( `[name="${shippingField.attr( 'name' ).replace( 'shipping_', 'billing_' )}"]` );

            if ( sameAsShipping === 'same_as_shipping' ) {
                this.syncField( shippingField, billingField );
            }
        } );
    }

    listenForBillingChanges(): void {
        jQuery( '[name^="billing_"]' ).not( '#billing_email' ).on( 'change', ( event, param ) => {
            // Only process this if a human changed the value
            // OR if cfw_store was passed as the first parameter (zip / address autocomplete)

            const validNonhumanEventSources = [ 'cfw_zip_change', 'cfw_address_autocompleted' ];

            if ( typeof event.originalEvent !== 'undefined' || validNonhumanEventSources.includes( param ) ) {
                const billingField =  jQuery( event.target );
                billingField.data( 'saved-value', billingField.val() );
            }
        } );
    }

    listenForSameAsShippingToggle(): void {
        const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]' );

        sameAsShipping.on( 'change', () => {
            this.maybeSyncShippingAddressToBillingAddress();
            this.maybeRestoreSessionValueToBillingAddress();
        } );
    }

    maybeSyncShippingAddressToBillingAddress(): void {
        const billToDifferentAddress = jQuery( 'input[name="bill_to_different_address"]:checked' );

        if ( !billToDifferentAddress.length ) {
            return;
        }

        // Only proceed if same as shipping address selected
        if ( billToDifferentAddress.val() !== 'same_as_shipping' ) {
            return;
        }

        jQuery( '.woocommerce-shipping-fields [name^="shipping_"]' ).each( ( i, element ) => {
            const shippingField  = jQuery( element );
            const billingField   = jQuery( `[name="${shippingField.attr( 'name' ).replace( 'shipping_', 'billing_' )}"]` );

            this.syncField( shippingField, billingField );
        } );
    }

    maybeRestoreSessionValueToBillingAddress(): void {
        const billToDifferentAddress = jQuery( 'input[name="bill_to_different_address"]:checked' );

        if ( !billToDifferentAddress.length ) {
            return;
        }

        // Only proceed if 'Use different billing address' selected
        if ( billToDifferentAddress.val() === 'same_as_shipping' ) {
            return;
        }

        jQuery( '[name^="billing_"]' ).not( '#billing_email' ).each( ( i, element ) => {
            const billingField = jQuery( element );
            let savedValue = billingField.data( 'saved-value' );
            const hasSavedValue = typeof savedValue !== 'undefined';
            const currentValue = billingField.val();

            if ( !hasSavedValue ) {
                return;
            }

            if ( savedValue === currentValue ) {
                return;
            }

            if ( savedValue === 'CFW_EMPTY' ) {
                savedValue = '';
            }

            billingField.val( savedValue ).trigger( 'cfw_garlic_store' );
            LoggingService.logEvent( 'Fired cfw_garlic_store event.' );

            // Prevent parsley errors on page load
            if ( savedValue !== '' ) {
                billingField.trigger( 'change' );
            }
        } );
    }

    /**
     *
     * @param srcField
     * @param destField
     */
    syncField( srcField: JQuery<HTMLElement>, destField: JQuery<HTMLElement> ): void {
        if ( typeof destField === 'undefined' ) {
            return;
        }

        if ( typeof srcField === 'undefined' ) {
            return;
        }

        const currentValue = destField.val();
        const newValue = srcField.val();

        if ( newValue === null ) {
            return;
        }

        if ( currentValue !== newValue ) {
            LoggingService.log( `Syncing field: ${srcField.attr( 'name' )} to ${destField.attr( 'name' )}` );

            destField.val( srcField.val() )
                .trigger( 'change' )
                .trigger( 'cfw_garlic_store' );

            LoggingService.logEvent( 'Fired cfw_garlic_store event.' );
        }
    }
}

export default BillingAddressSyncService;
