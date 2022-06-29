import FieldValidationRefresher from '../Interfaces/FieldValidationRefresher';
import DataService              from './DataService';

class ZipAutocompleteService {
    protected fieldValidationRefresher: FieldValidationRefresher;

    constructor( fieldValidationRefresher: FieldValidationRefresher ) {
        this.fieldValidationRefresher = fieldValidationRefresher;
        this.setZipAutocompleteHandlers();
    }

    /**
     * Attach change events to postcode fields
     */
    setZipAutocompleteHandlers(): void {
        if ( DataService.getSetting( 'enable_zip_autocomplete' ) === true ) {
            jQuery( document.body ).on( 'input change paste', '#shipping_postcode, #billing_postcode', this.autoCompleteCityState.bind( this ) );
        }
    }

    autoCompleteCityState( e ): void {
        if ( typeof e.originalEvent === 'undefined' ) {
            return;
        }

        const type = e.currentTarget.id.split( '_' )[ 0 ]; // either shipping or billing
        const zip = e.currentTarget.value.trim();
        const val = jQuery( `#${type}_country` ).val();
        const country = typeof val === 'undefined' || val == null ? '' : val.toString();

        /**
         * Unfortunately, some countries copyright their zip codes
         * Meaning that you can only look up by the first 3 characters which
         * does not provide enough specificity so we skip them
         *
         * This is an incomplete list. Just hitting some big ones here.
         */
        const incompatibleCountries = [ 'GB', 'CA', 'NL', 'BE' ];

        if ( incompatibleCountries.indexOf( country ) === -1 ) {
            this.getZipData( country, zip, type );
        }
    }

    protected getZipData( country: string, zip: string, type: string ): void {
        jQuery.ajax( {
            url: `https://api.zippopotam.us/${country}/${zip}`,
            dataType: 'json',
            success: ( result ) => {
                const { 'place name': city, 'state abbreviation': state } = result.places[ 0 ];

                const stateField = jQuery( `[name="${type}_state"]:visible` );

                // Cleanup Parsley messages
                stateField.val( state ).trigger( 'change', [ 'cfw_zip_change' ] );

                this.fieldValidationRefresher.refreshField( stateField.get( 0 ) );

                // If there's more than one result, don't autocomplete city
                // This prevents crappy autocompletes
                if ( result.places.length !== 1 ) {
                    return;
                }

                const cityField = jQuery( `#${type}_city` );

                // Cleanup Parsley messages
                cityField.val( city ).trigger( 'change', [ 'cfw_zip_change' ] );

                this.fieldValidationRefresher.refreshField( cityField.get( 0 ) );
            },
        } );
    }
}

export default ZipAutocompleteService;
