import Utilities                  from '../../frontend/GoogleAddressAutocomplete/Utilities';
import { unitedStatesComponents } from './mockData/unitedStates';

test( 'getComponentValueByType', () => {
    expect( Utilities.getComponentValueByType( 'asdfas', unitedStatesComponents() ) ).toBe( '' );
    expect( Utilities.getComponentValueByType( 'street_number', unitedStatesComponents() ) ).toBe( '8525' );
    expect( Utilities.getComponentValueByType( 'political', unitedStatesComponents() ) ).toBe( '' );
    expect( Utilities.getComponentValueByType( 'administrative_area_level_2', unitedStatesComponents() ) ).toBe( 'Dallas County' );
} );

test( 'getFirstComponentValueByType', () => {
    expect( Utilities.getFirstComponentValueByType( [ 'a', 'b', 'c' ], unitedStatesComponents() ) ).toBe( '' );
    expect( Utilities.getFirstComponentValueByType( [ 'a', 'country', 'c' ], unitedStatesComponents() ) ).toBe( 'US' );
    expect( Utilities.getFirstComponentValueByType( [ 'postal_code', 'b', 'country' ], unitedStatesComponents() ) ).toBe( '75218' );
    expect( Utilities.getFirstComponentValueByType( [ 'postal_code' ], unitedStatesComponents() ) ).toBe( '75218' );
    expect( Utilities.getFirstComponentValueByType( [ '' ], unitedStatesComponents() ) ).toBe( '' );
} );
