import Utilities from '../Utilities';

export default class StreetNameStrategy {
    public constructor(  protected components: google.maps.GeocoderAddressComponent[], protected formattedAddress: string  ) {}

    public getValue(): string {
        return Utilities.getComponentValueByType( 'route', this.components );
    }
}
