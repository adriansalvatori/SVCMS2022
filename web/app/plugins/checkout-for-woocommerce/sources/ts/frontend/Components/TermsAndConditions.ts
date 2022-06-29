class TermsAndConditions {
    constructor() {
        this.setTermsAndConditionsListener();
    }

    /**
     * Handle showing / hiding terms and conditions
     */
    setTermsAndConditionsListener(): void {
        const termsAndConditionsLink = jQuery( '.woocommerce-terms-and-conditions-link' );
        const termsAndConditionsContent = jQuery( '.woocommerce-terms-and-conditions' );

        termsAndConditionsLink.on( 'click', ( e ) => {
            e.preventDefault();

            termsAndConditionsContent.slideToggle( 300 );
        } );
    }
}

export default TermsAndConditions;
