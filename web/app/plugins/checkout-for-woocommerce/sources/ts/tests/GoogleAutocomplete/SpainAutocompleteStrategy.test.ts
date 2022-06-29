import SpainAutocompleteStrategy         from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/SpainAutocompleteStrategy';
import { spainAddress, spainComponents } from './mockData/spain';

const strategy = new SpainAutocompleteStrategy( spainComponents(), spainAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( 'Carrer Benidorm, 3' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( '' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Calp' );
} );

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'A' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( '03710' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'ES' );
} );
