/* eslint-disable prefer-regex-literals */
import Utilities from '../Utilities';

export default class HouseNumberStrategy {
    public constructor(  protected components: google.maps.GeocoderAddressComponent[], protected formattedAddress: string  ) {}

    public getValue(): string {
        // Process <subpremise>/<street number> <route> formats
        // get all the user entered values before a match with the street name;
        // group #1 = unit number, group #2 = street number
        // eslint-disable-next-line no-useless-escape
        const results = RegExp( '^(.*?)\/(.*?) ' ).exec( this.formattedAddress );

        // If this is an array, format was unit/house number format
        if ( Array.isArray( results ) ) {
            return `${results[ 1 ]}/${results[ 2 ]}`;
        }

        const streetNumber   = Utilities.getComponentValueByType( 'street_number', this.components );
        const premise        = Utilities.getComponentValueByType( 'premise', this.components );
        const subpremise     = Utilities.getComponentValueByType( 'subpremise', this.components );
        const simpleResult   = streetNumber || premise || subpremise;

        if ( simpleResult ) {
            return simpleResult;
        }

        const houseNumberResults = RegExp( '^(\\d+)\\s' ).exec( this.formattedAddress );

        return Array.isArray( houseNumberResults ) ? `${houseNumberResults[ 1 ]}` : '';
    }
}
