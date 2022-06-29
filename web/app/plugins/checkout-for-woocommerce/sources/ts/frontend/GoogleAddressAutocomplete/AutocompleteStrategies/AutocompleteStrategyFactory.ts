import Utilities                       from '../Utilities';
import AutocompleteStrategy            from './AutocompleteStrategy';
import CanadaAutocompleteStrategy      from './CanadaAutocompleteStrategy';
import GermanyAutocompleteStrategy     from './GermanyAutocompleteStrategy';
import NetherlandsAutocompleteStrategy from './NetherlandsAutocompleteStrategy';
import NewZealandAutocompleteStrategy  from './NewZealandAutocompleteStrategy';
import SpainAutocompleteStrategy       from './SpainAutocompleteStrategy';
import SwedenAutocompleteStrategy      from './SwedenAutocompleteStrategy';

export default class AutocompleteStrategyFactory {
    public static get( components: google.maps.GeocoderAddressComponent[], formattedAddress: string ): AutocompleteStrategy {
        const country = Utilities.getComponentValueByType( 'country', components );

        if ( country === 'NZ' ) {
            return new NewZealandAutocompleteStrategy( components, formattedAddress );
        }

        if ( country === 'NL' ) {
            return new NetherlandsAutocompleteStrategy( components, formattedAddress );
        }

        if ( country === 'CA' ) {
            return new CanadaAutocompleteStrategy( components, formattedAddress );
        }

        if ( country === 'ES' ) {
            return new SpainAutocompleteStrategy( components, formattedAddress );
        }

        if ( country === 'DE' ) {
            return new GermanyAutocompleteStrategy( components, formattedAddress );
        }

        if ( country === 'SE' ) {
            return new SwedenAutocompleteStrategy( components, formattedAddress );
        }

        return new AutocompleteStrategy( components, formattedAddress );
    }
}
