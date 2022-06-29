import LoggingService from '../frontend/Services/LoggingService';

const usedNames = new Set();
const namespace = 'checkoutwc';

// Disable ban of type Function because we're truly only concerned that a Function is given.
// eslint-disable-next-line @typescript-eslint/ban-types
export default function cfwAddPublicFunction( name: string, func: Function ): void {
    const valid = /^[A-z]{1}[0-z]*$/.test( name );

    if ( !valid ) {
        LoggingService.logError( `function name is invalid: ${name}` );
        return;
    }

    if ( usedNames.has( name ) ) {
        LoggingService.logError( `function name is already used: ${name}` );
        return;
    }

    if ( !Object.prototype.hasOwnProperty.call( window, namespace ) ) {
        window[ namespace ] = {};
    }

    window[ namespace ][ name ] = func;

    usedNames.add( name );
}
