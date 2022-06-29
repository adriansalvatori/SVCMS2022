class Accordion {
    private readonly _targetSelector;

    /**
     * Default selector is the class used for all radio reveal groups
     * SO, when using you have to remember that .cfw-radio-reveal-group matches
     * multiple parent containers for different accordions
     *
     * @param targetSelector
     */
    constructor( targetSelector = '.cfw-radio-reveal-group' ) {
        this._targetSelector = targetSelector;

        this.setListeners();
    }

    setListeners() {
        jQuery( document.body ).on( 'change', `${this._targetSelector} .cfw-radio-reveal-title-wrap :radio`, ( e ) => {
            this.showContent( e.target );
        } );

        jQuery( document.body ).on( 'updated_checkout', () => {
            jQuery( this._targetSelector ).each( ( index, element ) => {
                this.showContent( jQuery( element ).find( ' .cfw-radio-reveal-title-wrap :radio:checked' ).first() );
            } );
        } );
    }

    showContent( target ) {
        const radioButton = jQuery( target );
        const parentRow = radioButton.parents( '.cfw-radio-reveal-li' ).first();

        if ( radioButton.is( ':checked' ) ) {
            parentRow.siblings( '.cfw-radio-reveal-li' ).removeClass( 'cfw-active' );
            parentRow.addClass( 'cfw-active' );
            parentRow.siblings( '.cfw-radio-reveal-li' ).find( '.cfw-radio-reveal-content:visible' ).slideUp( 300 );
            parentRow.find( '.cfw-radio-reveal-content:hidden' ).slideDown( 300 );
        } else {
            parentRow.removeClass( 'cfw-active' );
        }
    }
}

export default Accordion;
