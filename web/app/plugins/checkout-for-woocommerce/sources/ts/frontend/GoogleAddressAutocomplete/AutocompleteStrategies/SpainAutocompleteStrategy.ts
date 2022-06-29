import Utilities            from '../Utilities';
import AutocompleteStrategy from './AutocompleteStrategy';

export default class SpainAutocompleteStrategy extends AutocompleteStrategy {
    public getState(): string {
        return Utilities.getComponentValueByType( 'administrative_area_level_2', this.components );
    }
}
