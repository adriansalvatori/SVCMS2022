import 'core-js/features/object/assign';
import 'ts-polyfill';
import { cfwDomReady }  from './_functions';
import DataService      from './frontend/Services/DataService';
import MapEmbedService  from './frontend/Services/MapEmbedService';

class ThankYou {
    constructor() {
        const map_embed_service = new MapEmbedService();

        cfwDomReady( () => {
            map_embed_service.setMapEmbedHandlers();

            jQuery( '.status-step-selected' ).prevAll().addClass( 'status-step-selected' );

            // Init runtime params
            DataService.initRunTimeParams();

            jQuery( '#cfw-mobile-cart-header' ).on( 'click', ( e ) => {
                e.preventDefault();
                jQuery( '#cfw-cart-summary-content' ).slideToggle( 300 );
                jQuery( '#cfw-expand-cart' ).toggleClass( 'active' );
            } );

            jQuery( window ).on( 'load', () => {
                jQuery( '#wpadminbar' ).appendTo( 'html' );

                // Remove the animation blocker
                jQuery( document.body ).removeClass( 'cfw-preload' );
            } );
        } );
    }
}

new ThankYou();
