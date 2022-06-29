import CityNoSublocalityLevel1Strategy from '../FieldStrategies/CityNoSublocalityLevel1Strategy';
import AutocompleteStrategy            from './AutocompleteStrategy';

export default class NetherlandsAutocompleteStrategy extends AutocompleteStrategy {
    public getCity():string {
        return new CityNoSublocalityLevel1Strategy( this.components ).getValue();
    }
}
