import CityNoSublocalityLevel1Strategy from '../FieldStrategies/CityNoSublocalityLevel1Strategy';
import Utilities                       from '../Utilities';
import AutocompleteStrategy            from './AutocompleteStrategy';

export default class NewZealandAutocompleteStrategy extends AutocompleteStrategy {
    public getAddress2(): string {
        return Utilities.getComponentValueByType( 'sublocality_level_1', this.components );
    }

    public getCity():string {
        return new CityNoSublocalityLevel1Strategy( this.components ).getValue();
    }
}
