import AutocompleteStrategyFactory                     from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/AutocompleteStrategyFactory';
import AutocompleteStrategy                            from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/AutocompleteStrategy';
import CanadaAutocompleteStrategy                      from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/CanadaAutocompleteStrategy';
import GermanyAutocompleteStrategy                     from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/GermanyAutocompleteStrategy';
import NetherlandsAutocompleteStrategy                 from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/NetherlandsAutocompleteStrategy';
import NewZealandAutocompleteStrategy                  from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/NewZealandAutocompleteStrategy';
import SpainAutocompleteStrategy                       from '../../frontend/GoogleAddressAutocomplete/AutocompleteStrategies/SpainAutocompleteStrategy';
import { canadaAddress, canadaComponents }             from './mockData/canada';
import { germanyAddress, germanyComponents }           from './mockData/germany';
import { netherlandsAddress, netherlandsComponents }   from './mockData/netherlands';
import { newZealandAddress, newZealandComponents }     from './mockData/newZealand';
import { spainAddress, spainComponents }               from './mockData/spain';
import { unitedStatesAddress, unitedStatesComponents } from './mockData/unitedStates';

test( 'AutocompleteStrategyFactory.ts United States', () => {
    expect( AutocompleteStrategyFactory.get( unitedStatesComponents(), unitedStatesAddress() ).constructor.name ).toBe( AutocompleteStrategy.name );
} );

test( 'AutocompleteStrategyFactory.ts Special Cases', () => {
    expect( AutocompleteStrategyFactory.get( canadaComponents(), canadaAddress() ).constructor.name ).toBe( CanadaAutocompleteStrategy.name );
    expect( AutocompleteStrategyFactory.get( germanyComponents(), germanyAddress() ).constructor.name ).toBe( GermanyAutocompleteStrategy.name );
    expect( AutocompleteStrategyFactory.get( netherlandsComponents(), netherlandsAddress() ).constructor.name ).toBe( NetherlandsAutocompleteStrategy.name );
    expect( AutocompleteStrategyFactory.get( newZealandComponents(), newZealandAddress() ).constructor.name ).toBe( NewZealandAutocompleteStrategy.name );
    expect( AutocompleteStrategyFactory.get( spainComponents(), spainAddress() ).constructor.name ).toBe( SpainAutocompleteStrategy.name );
} );
