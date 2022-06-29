import Utilities from '../Utilities';

export default class CityStrategy {
    public constructor( protected components: google.maps.GeocoderAddressComponent[] ) {}

    public getValue(): string {
        const order = [
            'sublocality_level_1',
            'locality',
            'postal_town',
            'administrative_area_level_2',
            'administrative_area_level_3',
        ];

        return Utilities.getFirstComponentValueByType( order, this.components );
    }
}
