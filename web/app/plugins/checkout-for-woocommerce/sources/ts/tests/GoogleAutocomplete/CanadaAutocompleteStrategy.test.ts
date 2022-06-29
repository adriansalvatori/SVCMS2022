import CanadaAutocompleteStrategy          from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/CanadaAutocompleteStrategy';
import { canadaAddress, canadaComponents } from './mockData/canada';

const strategy = new CanadaAutocompleteStrategy( canadaComponents(), canadaAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( '145 Richmond St W' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( '' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Toronto' );
} );

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'ON' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( 'M5H 2L2' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'CA' );
} );
