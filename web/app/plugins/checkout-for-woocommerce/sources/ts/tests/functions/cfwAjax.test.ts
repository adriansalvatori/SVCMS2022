import DataService from '../../frontend/Services/DataService';
import cfwAjax     from '../../functions/cfwAjax';

jest.mock( '../../frontend/Services/DataService' );
DataService.getCheckoutParam = ( param ) => ( param === 'wc_ajax_url' ? '/?wc-ajax=%%endpoint%%' : '' );

const { mock: { calls: ajaxCalls } } = jest.spyOn( jQuery, 'ajax' ).mockImplementation(); // mock jQuery.ajax and get array of args called
const getLastAjaxCallArgs            = () => ajaxCalls[ ajaxCalls.length - 1 ][ 0 ];

test( 'cfwAjax adds overrides', () => {
    cfwAjax( 'test-id', { type: 'POST' } );

    const { dataType, cache, url, error } = getLastAjaxCallArgs();

    expect( dataType ).toBe( 'json' );
    expect( cache ).toBe( false );
    expect( /^\/\?wc-ajax=test-id&nocache=\d+$/.exec( url ) ).toHaveLength( 1 );
    expect( Array.isArray( error ) ).toBe( true );
    expect( error ).toHaveLength( 1 );
    expect( error[ 0 ] ).toBeInstanceOf( Function );
} );

test( 'cfwAjax passes through params', () => {
    cfwAjax( 'test2', { data: 'woot', type: 'POST' } );

    const { type, data } = getLastAjaxCallArgs();

    expect( type ).toBe( 'POST' );
    expect( data ).toBe( 'woot' );
} );

test( 'cfwAjax includes params.error in error array', () => {
    const simpleErrorFunc  = ( xhr: any, textStatus: string, errorThrown: string ) => { /** woot */ };
    const simpleErrorFunc2 = ( xhr: any, textStatus: string, errorThrown: string ) => { /** woot */ };

    cfwAjax( 'test3', { error: simpleErrorFunc } );
    expect( getLastAjaxCallArgs().error[ 0 ] ).toBe( simpleErrorFunc );

    cfwAjax( 'test4', { error: [ simpleErrorFunc, simpleErrorFunc2 ] } );

    const { error } = getLastAjaxCallArgs();

    expect( error[ 0 ] ).toBe( simpleErrorFunc );
    expect( error[ 1 ] ).toBe( simpleErrorFunc2 );
    expect( error[ 2 ] ).toBeInstanceOf( Function );
} );
