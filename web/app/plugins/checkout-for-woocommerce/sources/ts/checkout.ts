import 'core-js/features/object/assign';
import 'ts-polyfill';
import { cfwDomReady, cfwDefineScrollToNotices } from './_functions';
import Main                                      from './frontend/Main';
import DataService                               from './frontend/Services/DataService';
import LoggingService                            from './frontend/Services/LoggingService';

declare let cfwEventData: any;

// selectWoo Shim
( function ( $ ) {
    $.fn.selectWoo = function ( options ) {
        LoggingService.logNotice( 'jQuery.selectWoo is not currently supported by CheckoutWC.' );
        return this;
    };
}( jQuery ) );

cfwDomReady( () => {
    const data = cfwEventData;
    const formEl = jQuery( data.elements.checkoutFormSelector );
    const breadcrumbEl = jQuery( data.elements.breadCrumbElId );
    const alertContainerEl = jQuery( data.elements.alertContainerId );
    const tabContainerEl = jQuery( data.elements.tabContainerElId );

    // Allow users to add their own Typescript Compatibility classes
    jQuery( document.body ).trigger( 'cfw_checkout_before_load' );
    LoggingService.logEvent( 'Fired cfw_checkout_before_load event.' );

    // Init runtime params
    DataService.initRunTimeParams();

    DataService.checkoutForm = formEl;

    cfwDefineScrollToNotices();

    // Kick it off!
    new Main( formEl, alertContainerEl, tabContainerEl, breadcrumbEl, data.settings );
} );
