import DataService             from '../Services/DataService';
import LoggingService          from '../Services/LoggingService';
import Action                  from './Action';

declare let wc_cart_fragments_params: any;

class UpdateSideCart extends Action {
    protected blockedElements: JQuery;

    protected supportsHTML5Storage: boolean;

    /**
     * @param blockedElements
     */
    constructor( blockedElements: JQuery ) {
        super( 'update_side_cart' );

        this.blockedElements = blockedElements;

        try {
            this.supportsHTML5Storage = ( 'sessionStorage' in window && window.sessionStorage !== null );
            window.sessionStorage.setItem( 'wc', 'test' );
            window.sessionStorage.removeItem( 'wc' );
            window.localStorage.setItem( 'wc', 'test' );
            window.localStorage.removeItem( 'wc' );
        } catch ( err ) {
            this.supportsHTML5Storage = false;
        }
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        if ( resp.cart_hash ) {
            this.setCartHash( resp.cart_hash );
        }

        if ( resp.result ) {
            jQuery( document.body ).trigger( 'wc_fragment_refresh' );
        }

        this.blockedElements.unblock();
    }

    /** Set the cart hash in both session and local storage */
    setCartHash( cartHash: string ): void {
        // eslint-disable-next-line camelcase
        const { cart_hash_key } = wc_cart_fragments_params;

        if ( this.supportsHTML5Storage ) {
            localStorage.setItem( cart_hash_key, cartHash );
            sessionStorage.setItem( cart_hash_key, cartHash );
        }
    }
}

export default UpdateSideCart;
