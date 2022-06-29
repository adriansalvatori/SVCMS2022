import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooCommerceAddressValidation extends Compatibility {
    constructor() {
        super( 'WooCommerceAddressValidation' );
    }

    load(): void {
        jQuery( document.body ).on( 'load cfw-after-tab-change', () => {
            this.reactivateBillingAddress();
        } );

        jQuery( '[type="radio"][name="bill_to_different_address"]' ).on( 'change click', () => {
            this.reactivateBillingAddress();
        } );
    }

    resizeWindow(): void {
        ( <any>window ).setTimeout( () => {
            jQuery( window ).resize();
        }, 400 );
    }

    reactivateBillingAddress(): void {
        if ( jQuery( '[type="radio"][name="bill_to_different_address"]:checked' ).val() === 'different_from_shipping' ) {
            this.deactivate_billing();
            this.activate_billing();

            this.resizeWindow();
        }
    }

    activate_billing(): void {
        const smartyui = jQuery( '.deactivated.smarty-addr-billing_address_1' );

        if ( smartyui.length ) {
            ( <any>smartyui ).push( smartyui[ 0 ].parentElement );
            smartyui.removeClass( 'deactivated' );
            smartyui.addClass( 'activated' );
            smartyui.show();
        }
    }

    deactivate_billing(): void {
        const smartyui = jQuery( '.smarty-addr-billing_address_1:visible' );
        const autocompleteui = jQuery( '.smarty-autocomplete.smarty-addr-billing_address_1' );

        if ( smartyui.length ) {
            smartyui.addClass( 'deactivated' );
            smartyui.parent().addClass( 'deactivated' );
            autocompleteui.addClass( 'deactivated' );
            smartyui.hide();
            smartyui.parent().hide();
            autocompleteui.hide();
        }
    }
}

export default WooCommerceAddressValidation;
