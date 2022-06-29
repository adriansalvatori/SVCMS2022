import DataService    from '../frontend/Services/DataService';
import LoggingService from '../frontend/Services/LoggingService';

const consoleSpy = jest.spyOn( global.console, 'log' ).mockImplementation( ( ...args ) => args );

let mockDebugMode = true;

jest.mock( '../frontend/Services/DataService' );

DataService.getCheckoutParam = ( param ) => param === 'cfw_debug_mode' && mockDebugMode;

describe( 'LoggingService.ts', () => {
    const errorMessage = 'errorMessage';
    const noticeMessage = 'noticeMessage';
    const eventMessage = 'eventMessage';
    const logMessage = 'logMessage';

    test( 'LoggingService.logError cfw_debug_mode=true', () => {
        LoggingService.logError( errorMessage );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${errorMessage} ⚠️` );
    } );

    test( 'LoggingService.logNotice cfw_debug_mode=true', () => {
        LoggingService.logNotice( noticeMessage );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${noticeMessage} ℹ️` );
    } );

    test( 'LoggingService.logEvent cfw_debug_mode=true', () => {
        LoggingService.logEvent( eventMessage );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${eventMessage} 🔈` );
    } );

    test( 'LoggingService.log cfw_debug_mode=true', () => {
        LoggingService.log( logMessage );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${logMessage}` );
    } );

    test( 'LoggingService.logError cfw_debug_mode=false', () => {
        mockDebugMode = false;
        LoggingService.logError( errorMessage );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${errorMessage} ⚠️` );
        consoleSpy.mockClear();
    } );

    test( 'LoggingService.logNotice cfw_debug_mode=false', () => {
        mockDebugMode = false;
        LoggingService.logNotice( noticeMessage );
        expect( consoleSpy ).not.toHaveBeenCalledWith( `CheckoutWC: ${noticeMessage} ℹ️` );
    } );

    test( 'LoggingService.logEvent cfw_debug_mode=false', () => {
        mockDebugMode = false;
        LoggingService.logEvent( eventMessage );
        expect( consoleSpy ).not.toHaveBeenCalledWith( `CheckoutWC: ${eventMessage} 🔈` );
    } );

    test( 'LoggingService.log cfw_debug_mode=false', () => {
        LoggingService.log( logMessage );
        expect( consoleSpy ).not.toHaveBeenCalledWith( `CheckoutWC: ${logMessage}` );
        LoggingService.log( logMessage, true );
        expect( consoleSpy ).toHaveBeenLastCalledWith( `CheckoutWC: ${logMessage}` );
    } );
} );
