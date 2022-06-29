import DataService    from '../frontend/Services/DataService';
import LoggingService from '../frontend/Services/LoggingService';

export default function cfwAjax( id: string, params: JQuery.AjaxSettings ): JQuery.jqXHR<any> {
    LoggingService.log( `AJAX request to endpoint: ${id}. ☄️` );

    const defaultErrorFunction = ( xhr: JQuery.jqXHR, textStatus: string, errorThrown: string ): void => {
        if ( textStatus !== 'abort' ) {
            LoggingService.logError( `cfwAjax ${id} Error: ${errorThrown} (${textStatus})` );
        }
    };

    const args     = { ...params };
    const endpoint = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', id );
    args.url       = `${endpoint}&nocache=${new Date().getTime()}`;
    args.dataType  = 'json';
    args.cache     = false;
    args.error     = [ params.error, defaultErrorFunction ].filter( Boolean ).flat(); // ensures 1d array of callbacks

    return jQuery.ajax( args );
}
