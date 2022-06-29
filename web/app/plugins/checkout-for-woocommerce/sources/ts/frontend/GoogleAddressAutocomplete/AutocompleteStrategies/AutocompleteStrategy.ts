import Address1Strategy                from '../FieldStrategies/Address1Strategy';
import CityStrategy                    from '../FieldStrategies/CityStrategy';
import HouseNumberStrategy             from '../FieldStrategies/HouseNumberStrategy';
import StreetNameStrategy              from '../FieldStrategies/StreetNameStrategy';
import Utilities                       from '../Utilities';

class AutocompleteStrategy {
    public constructor( protected components: google.maps.GeocoderAddressComponent[], protected formattedAddress: string ) {}

    public getHouseNumber(): string {
        return new HouseNumberStrategy( this.components, this.formattedAddress ).getValue();
    }

    public getStreetName(): string {
        return new StreetNameStrategy( this.components, this.formattedAddress ).getValue();
    }

    public getAddress1(): string {
        return new Address1Strategy( this.components, this.formattedAddress ).getValue();
    }

    /**
     * We've taken the stance that if someone is accepting
     * a new suggested place from Google then the likelihood
     * they want to keep the address 2 data is very slim.
     */
    public getAddress2(): string {
        return '';
    }

    public getCity(): string {
        return new CityStrategy( this.components ).getValue();
    }

    public getState(): string {
        return Utilities.getComponentValueByType( 'administrative_area_level_1', this.components );
    }

    public getPostcode(): string {
        return Utilities.getComponentValueByType( 'postal_code', this.components );
    }

    public getCountry(): string {
        return Utilities.getComponentValueByType( 'country', this.components );
    }
}

export default AutocompleteStrategy;
