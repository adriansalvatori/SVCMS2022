class AddableFields {
    constructor() {
        jQuery( document.body ).on( 'click', '.cfw-add-field', function () {
            jQuery( this ).next( '[id$=_field]' ).slideDown( 300, function () {
                jQuery( this ).removeClass( 'cfw-hidden' );
            } );
            jQuery( this ).slideUp( 300 );
        } );

        jQuery( '#billing_company, #shipping_company, #billing_address_2, #shipping_address_2' ).each( function () {
            if ( jQuery( this ).val() !== '' ) {
                const fieldWrapper = jQuery( this ).parents( '[id$=_field]' );
                fieldWrapper.slideDown( 300, function () {
                    jQuery( this ).removeClass( 'cfw-hidden' );
                } );
                fieldWrapper.siblings( '.cfw-add-field' ).slideUp( 300 );
            }
        } );
    }
}

export default AddableFields;
