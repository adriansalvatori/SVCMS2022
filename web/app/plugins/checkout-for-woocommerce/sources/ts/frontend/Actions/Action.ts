import DataService    from '../Services/DataService';
import LoggingService from '../Services/LoggingService';

/**
 * Base class for our ajax handling. Child classes will extend this and override the response function and implement their
 * own custom solutions for the php side of actions
 */
abstract class Action {
    /**
     * @type {string}
     * @private
     */
    protected id: string;

    /**
     * @param id
     */
    protected constructor( id: string ) {
        this.id = id;

        LoggingService.log( `Running ${this.id} action. ☄️` );
    }

    /**
     * Fire the ajax request
     *
     * @param data
     */
    load( data: any ): void {
        const currentTime = new Date();
        const n = currentTime.getTime();
        const url = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', this.id );

        jQuery.ajax( {
            type: 'POST',
            url: `${url}&nocache=${n}`,
            data,
            success: this.response.bind( this ),
            error: this.error.bind( this ),
            complete: this.complete.bind( this ),
            dataType: 'json',
            cache: false,
        } );
    }

    /**
     * Our ajax response handler. Overridden in child classes
     * @param resp
     */
    abstract response( resp: any ): void;

    /**
     * Our ajax error handler. Overridden in child classes
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    error( xhr: any, textStatus: string, errorThrown: string ): void {
        if ( textStatus !== 'abort' ) {
            LoggingService.logError( `${this.constructor.name} Error: ${errorThrown} (${textStatus}` );
        }
    }

    complete( xhr: any, textStatus: string ): void {}
}

export default Action;
