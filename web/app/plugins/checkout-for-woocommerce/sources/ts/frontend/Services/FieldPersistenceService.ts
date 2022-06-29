/**
 * Field Persistence Service
 */
import DataService    from './DataService';
import LoggingService from './LoggingService';

class FieldPersistenceService {
    constructor( form: JQuery ) {
        if ( DataService.getSetting( 'enable_field_persistence' ) !== true ) {
            return;
        }

        const excludes = DataService.getSetting( 'field_persistence_excludes' );

        form.garlic( {
            events: [ 'input', 'change', 'click', 'paste', 'focus', 'cfw_garlic_store' ],
            destroy: false,
            excluded: excludes instanceof Array ? excludes.join( ', ' ) : '',
            onRetrieve: this.onRetrieve.bind( this ),
        } );

        this.setListeners();
    }

    setListeners(): void {
        // After Parsley Service resets field
        jQuery( document.body ).on( 'cfw-after-field-country-to-state-changed', ( e ) => {
            jQuery( e.target ).garlic();
        } );
    }

    onRetrieve( element: JQuery, retrievedValue ): void {
        jQuery( document.body ).trigger( 'cfw_garlic_retrieved', [ element, retrievedValue ] );
        LoggingService.logEvent( `Fired cfw_garlic_retrieved event. Element: ${element.attr( 'name' )} Value: ${retrievedValue}` );
    }
}

export default FieldPersistenceService;
