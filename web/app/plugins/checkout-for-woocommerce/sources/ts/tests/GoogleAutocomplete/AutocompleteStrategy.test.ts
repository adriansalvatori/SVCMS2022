import AutocompleteStrategy                            from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/AutocompleteStrategy';
import { unitedStatesAddress, unitedStatesComponents } from './mockData/unitedStates';

const strategy = new AutocompleteStrategy( unitedStatesComponents(), unitedStatesAddress() );

test( 'getAddress1', () => {
    expect( strategy.getAddress1() ).toBe( '8525 Garland Rd' );
} );

test( 'getAddress2', () => {
    expect( strategy.getAddress2() ).toBe( '' );
} );

test( 'getCity', () => {
    expect( strategy.getCity() ).toBe( 'Dallas' );
} );

test( 'getState', () => {
    expect( strategy.getState() ).toBe( 'TX' );
} );

test( 'getPostcode', () => {
    expect( strategy.getPostcode() ).toBe( '75218' );
} );

test( 'getCountry', () => {
    expect( strategy.getCountry() ).toBe( 'US' );
} );
