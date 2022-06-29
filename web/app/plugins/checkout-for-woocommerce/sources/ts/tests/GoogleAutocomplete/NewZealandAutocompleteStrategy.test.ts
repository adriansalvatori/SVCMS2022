import NewZealandAutocompleteStrategy              from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/NewZealandAutocompleteStrategy';
import { newZealandAddress, newZealandComponents } from './mockData/newZealand';

const strategy = new NewZealandAutocompleteStrategy( newZealandComponents(), newZealandAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( '147 Quay St' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( 'Auckland CBD' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Auckland' );
} );

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'Auckland' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( '1010' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'NZ' );
} );
