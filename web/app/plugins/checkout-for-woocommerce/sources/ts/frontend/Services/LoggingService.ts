import DataService from './DataService';

class LoggingService {
    static logError( message: string, object: unknown = null ): void {
        LoggingService.log( `${message} ⚠️`, true, object );
    }

    static logNotice( message: string, object: unknown = null ): void {
        LoggingService.log( `${message} ℹ️`, false, object );
    }

    static logEvent( message: string, object: unknown = null ): void {
        LoggingService.log( `${message} 🔈`, false, object );
    }

    static log( message: string, force = false, object: unknown = null ): void {
        if ( force || DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: ${message}` );

            if ( object ) {
                // eslint-disable-next-line no-console
                console.log( object );
            }
        }
    }
}

export default LoggingService;
