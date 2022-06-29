import { Md5 } from 'ts-md5/dist/md5';

const debounce = require( 'debounce' );

class Alert {
    public type: 'error' | 'notice' | 'success';

    public message: string;

    public cssClass: string;

    public alertContainer: JQuery<HTMLElement>;

    public temporary: boolean;

    private readonly _debouncedScrollToNotices;

    /**
     * @param type
     * @param message
     * @param cssClass
     * @param temporary
     */
    constructor( type: 'error' | 'notice' | 'success', message: string, cssClass: string, temporary = false ) {
        this.type = type;
        this.message = message;
        this.cssClass = cssClass;
        this.alertContainer = jQuery( '#cfw-alert-container' );
        this.temporary = temporary;
    }
}

export default Alert;
