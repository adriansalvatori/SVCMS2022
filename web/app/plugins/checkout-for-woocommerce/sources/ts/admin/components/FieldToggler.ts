class FieldToggler {
    private togglerSelector: string;

    private toggledFieldsSelector: string;

    constructor( togglerSelector: string, toggledFieldsSelector: string ) {
        this.togglerSelector = togglerSelector;
        this.toggledFieldsSelector = toggledFieldsSelector;

        jQuery( togglerSelector ).on( 'change', this.showHide.bind( this ) );

        this.showHide();
    }

    showHide(): void {
        const toggledFields = jQuery( this.toggledFieldsSelector );
        const toggledFieldsParents = toggledFields.parents( '.cfw-admin-field-container, tr' );

        if ( jQuery( this.togglerSelector ).is( ':checked' ) && jQuery( this.togglerSelector ).is( ':enabled' ) ) {
            toggledFieldsParents.show();
            toggledFields.show();

            toggledFields.trigger( 'change' );
        } else {
            toggledFieldsParents.hide();
            toggledFields.hide();
        }
    }
}

export default FieldToggler;
