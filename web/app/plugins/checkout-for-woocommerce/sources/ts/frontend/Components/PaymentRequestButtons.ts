class PaymentRequestButtons {
    private expressButtonContainer: any;

    private expressButtonSeparator: any;

    constructor() {
        this.expressButtonContainer = jQuery( '#cfw-payment-request-buttons' );
        this.expressButtonSeparator = jQuery( '#payment-info-separator-wrap .pay-button-separator' );

        // Catch buttons as soon as possible
        if ( this.maybeShowExpressButtons() ) {
            return;
        }

        jQuery( window ).on( 'load', () => {
            if ( this.maybeShowExpressButtons() ) {
                return;
            }

            // Handle very slow loading buttons
            let iterations = 0;
            const timer = setInterval( () => {
                if ( this.maybeShowExpressButtons() || iterations > 5 ) {
                    clearInterval( timer );
                }

                iterations += 1;
            }, 1000 );
        } );
    }

    hideExpressButtons(): void {
        this.expressButtonContainer.css( 'position', 'absolute' ).css( 'visibility', 'hidden' );
        this.expressButtonSeparator.hide();
    }

    showExpressButtons(): void {
        this.expressButtonContainer.css( 'position', 'relative' ).css( 'visibility', 'visible' );
        this.expressButtonSeparator.show();
    }

    maybeShowExpressButtons(): boolean {
        const hasButtons = this.hasButtons();

        if ( hasButtons ) {
            this.showExpressButtons();
        }

        return hasButtons;
    }

    hasButtons(): boolean {
        let hasButtons = false;
        const potentialButtons = this.expressButtonContainer.children().not( 'h2, .blockUI' );

        potentialButtons.each( ( index, element ) => {
            if ( jQuery( element ).get( 0 ).getBoundingClientRect().height > 0 ) {
                hasButtons = true;
            }
        } );

        return hasButtons;
    }
}

export default PaymentRequestButtons;
