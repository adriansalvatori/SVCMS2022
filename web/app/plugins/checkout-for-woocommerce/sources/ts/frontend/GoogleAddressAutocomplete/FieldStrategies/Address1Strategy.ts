import cfwBuildAddress1    from '../../../functions/cfwBuildAddress1';
import Utilities           from '../Utilities';
import HouseNumberStrategy from './HouseNumberStrategy';
import StreetNameStrategy  from './StreetNameStrategy';

export default class Address1Strategy {
    public constructor(  protected components: google.maps.GeocoderAddressComponent[], protected formattedAddress: string  ) {}

    public getValue(): string {
        const country     = Utilities.getComponentValueByType( 'country', this.components );
        const houseNumber = new HouseNumberStrategy( this.components, this.formattedAddress ).getValue();
        const streetName  = new StreetNameStrategy( this.components, this.formattedAddress ).getValue();

        return cfwBuildAddress1( country, houseNumber, streetName );
    }
}
