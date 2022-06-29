import DataService from '../Services/DataService';

const { parseFullName } = require( 'parse-full-name' );

class FullName {
    constructor() {
        if ( !DataService.getSetting( 'use_fullname_field' ) ) {
            return;
        }
        this.setupListeners( 'shipping' );
        this.setupListeners( 'billing' );

        ( <any>window ).Parsley.addValidator( 'fullname', {
            validateString( value, type ) {
                // Test if value matches regex
                return /^(.+)\s(.+)$/.test( value );
            },
            messages: {
                en: DataService.getMessage( 'invalid_full_name_message' ),
            },
        } );
    }

    setupListeners( prefix: string ): void {
        jQuery( document.body ).on( 'change', `#${prefix}_full_name`, function () {
            // Use parse-full-name to parse full name into first and last name
            const name = parseFullName( jQuery( this ).val() );

            jQuery( `#${prefix}_first_name` ).val( name.first );
            jQuery( `#${prefix}_last_name` ).val( name.last );
        } );

        jQuery( window ).on( 'load', () => {
            const name = parseFullName( jQuery( `#${prefix}_full_name` ).val() );
            jQuery( `#${prefix}_first_name` ).val( name.first );
            jQuery( `#${prefix}_last_name` ).val( name.last );
        } );
    }
}

export default FullName;
