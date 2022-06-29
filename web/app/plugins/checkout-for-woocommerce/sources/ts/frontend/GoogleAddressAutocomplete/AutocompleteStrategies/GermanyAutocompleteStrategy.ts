import Utilities            from '../Utilities';
import AutocompleteStrategy from './AutocompleteStrategy';

export default class GermanyAutocompleteStrategy extends AutocompleteStrategy {
    public getCity():string {
        // Let's try out using the long_name ¯\_(ツ)_/¯ HelpScout ticket #11086
        return [ 'locality', 'postal_town', 'administrative_area_level_2', 'administrative_area_level_3' ]
            .map( ( type ) => Utilities.getComponentByType( type, this.components )?.long_name )
            .find( Boolean );
    }
}
