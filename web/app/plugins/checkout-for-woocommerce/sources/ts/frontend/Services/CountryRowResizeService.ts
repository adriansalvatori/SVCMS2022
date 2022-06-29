import LoggingService from './LoggingService';

class CountryRowResizeService {
    constructor() {
        jQuery( document.body ).on( 'country_to_state_changed', () => {
            jQuery( '#shipping_country_field, #billing_country_field' ).each( function () {
                const parent_row = jQuery( this ).parent( '.cfw-input-wrap-row' );

                parent_row.css( { 'flex-wrap': 'nowrap' } );
            } );

            ( <any>window ).setTimeout( () => {
                jQuery( '#shipping_country_field, #billing_country_field' ).each( function () {
                    const parent_row = jQuery( this ).parent( '.cfw-input-wrap-row' );
                    const columns = parent_row.find( '> div' );

                    columns.each( function () {
                        const column = jQuery( this );

                        /**
                         * This logic is hard to grok
                         *
                         * But essentially what we are doing is finding whether
                         * the column has any non-hidden inputs.
                         *
                         * If the number of non-hidden inputs is 0, then it only has hidden inputs and thus
                         * the column should be hidden
                         */
                        if ( column.find( ':input[type!="hidden"]' ).length == 0 ) {
                            column.css( { 'flex-basis': '0%', opacity: 0 } ).addClass( 'cfw-column-hidden' ).parent().addClass( 'cfw-state-hidden' );
                        } else {
                            column.css( { 'flex-basis': '', opacity: 1 } ).removeClass( 'cfw-column-hidden' ).parent().removeClass( 'cfw-state-hidden' );
                        }
                    } );

                    const visible_columns = parent_row.find( '> div' ).not( '.cfw-column-hidden' );
                    const column_grid_x = Math.floor( 12 / visible_columns.length );

                    visible_columns.each( function () {
                        const column = jQuery( this );

                        column.removeClass( ( index, className ) => ( className.match( /(^|\s)col-lg-\d+/g ) || [] ).join( ' ' ) );

                        column.addClass( `col-lg-${column_grid_x}` );
                    } );

                    /**
                     * This is timed to roughly match the transition on columns
                     * - transition: all 3s ease-in-out;
                     */
                    ( <any>window ).setTimeout( () => {
                        parent_row.css( { 'flex-wrap': '' } );
                    }, 300 );
                } );
            } );
        } ).trigger( 'country_to_state_changed' );
        LoggingService.logEvent( 'Fired country_to_state_changed event.' );
    }
}

export default CountryRowResizeService;
