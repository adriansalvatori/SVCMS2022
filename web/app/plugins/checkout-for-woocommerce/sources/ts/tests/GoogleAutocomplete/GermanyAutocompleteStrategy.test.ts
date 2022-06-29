import GermanyAutocompleteStrategy           from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/GermanyAutocompleteStrategy';
import { germanyAddress, germanyComponents } from './mockData/germany';

const strategy = new GermanyAutocompleteStrategy( germanyComponents(), germanyAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( 'Balatonstraße 46' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( '' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Berlin' );
} );

// This is a smell, Germany doesn't have states and we hide the field.
// Decoupling this will have to happen wherever the getState is being called.
// Likely a situation where something else is calling get state and instead the Strategy should be doing something.

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'BE' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( '10319' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'DE' );
} );
