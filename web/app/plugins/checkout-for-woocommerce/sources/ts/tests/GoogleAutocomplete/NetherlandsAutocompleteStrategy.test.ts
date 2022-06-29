import NetherlandsAutocompleteStrategy               from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/NetherlandsAutocompleteStrategy';
import { netherlandsAddress, netherlandsComponents } from './mockData/netherlands';

const strategy = new NetherlandsAutocompleteStrategy( netherlandsComponents(), netherlandsAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( 'Herengracht 341' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( '' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Amsterdam' );
} );

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'NH' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( '1016 AZ' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'NL' );
} );
