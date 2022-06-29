import AutocompleteStrategyFactory from '../GoogleAddressAutocomplete/AutocompleteStrategies/AutocompleteStrategyFactory';
import FieldValidationRefresher    from '../Interfaces/FieldValidationRefresher';
import DataService                 from './DataService';
import LoggingService              from './LoggingService';

/* global google */

class GoogleAddressAutocompleteService {
    private _fieldValidator: FieldValidationRefresher;

    constructor( fieldValidator: FieldValidationRefresher ) {
        if ( !DataService.getSetting( 'enable_address_autocomplete' ) ) {
            return;
        }

        this._fieldValidator = fieldValidator;

        if ( typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined' || typeof google.maps.places.Autocomplete === 'undefined' ) {
            LoggingService.logError( 'CheckoutWC: Could not load Google Maps object.' );
            return;
        }

        if ( DataService.getSetting( 'needs_shipping_address' ) === true ) {
            this.initAutocomplete( 'shipping_', 'shipping_address_1', DataService.getSetting( 'address_autocomplete_shipping_countries' ) );
        }

        this.initAutocomplete( 'billing_', 'billing_address_1', DataService.getSetting( 'address_autocomplete_billing_countries' )  );
    }

    initAutocomplete( prefix: string, mountId: string, countryRestrictions?: string|string[] ): void {
        const field = document.getElementById( mountId ) as HTMLInputElement;

        if ( !field ) {
            return;
        }

        field.autocomplete = 'new-password';

        const options = {
            fields: [ 'address_component' ],
            types: [ DataService.getSetting( 'google_address_autocomplete_type' ) ],
        };

        const autocomplete =  new google.maps.places.Autocomplete( field, options );

        if ( countryRestrictions ) {
            autocomplete.setComponentRestrictions( { country: countryRestrictions } );
        }

        autocomplete.addListener( 'place_changed', () => this.fillAddress( prefix, autocomplete, field ) );
    }

    fillAddress( prefix: string, autocomplete: google.maps.places.Autocomplete, { value: formattedAddress }: HTMLInputElement ): void {
        const { address_components: components } = autocomplete.getPlace();

        if ( !components ) {
            return;
        }

        const strategy = AutocompleteStrategyFactory.get( components, formattedAddress );

        this.queueStateUpdate( prefix, strategy.getState() );

        this.updateField( `${prefix}address_1`, strategy.getAddress1() );
        this.updateField( `${prefix}address_2`, strategy.getAddress2() );
        this.updateField( `${prefix}city`, strategy.getCity() );
        this.updateField( `${prefix}postcode`, strategy.getPostcode() );
        this.updateField( `${prefix}country`, strategy.getCountry() );
    }

    updateField( id: string, value: string ): void {
        const field = document.getElementById( id );

        if ( !field ) {
            return;
        }

        jQuery( field ).val( value ).trigger( 'change', [ 'cfw_address_autocompleted' ] );

        this._fieldValidator.refreshField( field );
    }

    queueStateUpdate( prefix: string, state: string ): void {
        jQuery( document.body ).one( 'country_to_state_changed', () => {
            setTimeout( () => {
                const stateField = jQuery( `#${prefix}state` );

                const noFuzzySearchNeeded = !stateField.is( 'select' ) || stateField.find( `option[value="${state}"]` ).length;
                const stateValue          = noFuzzySearchNeeded ? state : stateField.find( `option:contains(${state})` ).val();

                stateField.val( stateValue );

                stateField.trigger( 'change', [ 'cfw_address_autocompleted' ] );

                this._fieldValidator.refreshField( stateField.get( 0 ) );
            } );
        } );
    }
}

export default GoogleAddressAutocompleteService;
