import Compatibility from '../Compatibility';

class Square extends Compatibility {
    constructor() {
        super( 'Square' );
    }

    load(): void {
        jQuery( document.body ).on( 'change', '#billing_postcode', this.enforcePostalCodeValue );
        jQuery( document.body ).on( 'change', '#shipping_postcode, #billing_postcode', this.enforcePostalCodeValue );
        jQuery( document.body ).on( 'change click', '[type="radio"][name="bill_to_different_address"]', this.enforcePostalCodeValue );

        jQuery( document.body ).on( 'updated_checkout cfw-remove-overlay', () => {
            this.enforcePostalCodeValue();

            // This is necessary because we don't have an event to
            // indicate when the payment form is loaded
            setTimeout( this.enforcePostalCodeValue, 750 );
            setTimeout( this.enforcePostalCodeValue, 1500 );
            setTimeout( this.enforcePostalCodeValue, 3000 );
            setTimeout( this.enforcePostalCodeValue, 6000 );
        } );

        jQuery( document.body ).on( 'cfw-after-tab-change', () => {
            ( <any>window ).wc_square_credit_card_payment_form_handler.payment_form.recalculateSize();
        } );
    }

    enforcePostalCodeValue(): void {
        // The hidden class lets us know that the postal code field is loaded because
        // Square doesn't add this class until after the payment form is loaded
        if ( typeof ( <any>window ).wc_square_credit_card_payment_form_handler !== 'undefined' && jQuery( '.wc-square-credit-card-card-postal-code-parent' ).hasClass( 'hidden' ) ) {
            const sameAsShipping = jQuery( 'input[name="bill_to_different_address"]:checked' ).val();

            if ( sameAsShipping === 'same_as_shipping' ) {
                ( <any>window ).wc_square_credit_card_payment_form_handler.payment_form.setPostalCode( jQuery( '#shipping_postcode' ).val() );
            } else {
                ( <any>window ).wc_square_credit_card_payment_form_handler.payment_form.setPostalCode( jQuery( '#billing_postcode' ).val() );
            }
        }
    }
}

export default Square;
