import Main           from '../Main';
import AlertService   from './AlertService';
import DataService    from './DataService';
import LoggingService from './LoggingService';

/**
 * EzTab Enum
 */
export enum EasyTab {
    CUSTOMER,
    SHIPPING,
    PAYMENT,
}

/**
 *
 */
class TabService {
    /**
     * @type {any}
     * @private
     */
    private _tabContainer: any;

    /**
     * @type {any}
     * @private
     */
    private _tabBreadcrumbContainer: any;

    /**
     * @type string
     * @private
     */
    private static _customerInformationTabId = 'cfw-customer-info'

    /**
     * @type string
     * @private
     */
    private static _shippingMethodTabId = 'cfw-shipping-method';

    /**
     * @type string
     * @private
     */
    private static _paymentMethodTabId = 'cfw-payment-method';

    /**
     * @type string
     * @private
     */
    private static _orderReviewTabId = 'cfw-order-review';

    /**
     * @param tabContainer
     * @param tabBreadcrumbContainer
     */
    constructor( tabContainer: any, tabBreadcrumbContainer: any ) {
        this.tabContainer = jQuery( tabContainer );
        this.tabBreadcrumbContainer = jQuery( tabBreadcrumbContainer );
    }

    /**
     * Maybe load the tabs. Returns true if tabs loaded, otherwise returns false
     *
     * @return boolean
     */
    maybeLoadTabs(): boolean {
        // Don't try to load tabs if tabs don't exist
        if ( !this.tabBreadcrumbContainer.length ) {
            // if tabs can't load, make all the tabs visible
            if ( !DataService.getSetting( 'enable_one_page_checkout' ) ) {
                jQuery( '.cfw-panel' ).css( 'opacity', '1' ).css( 'display', 'block' );
            }

            return false;
        }

        try {
            // Initialize tabs
            this.tabContainer.easytabs( {
                animate: false,
                defaultTab: 'li.tab.cfw-default-tab',
                tabs: 'ul > li.tab',
            } );

            // After tab switch, scroll to the top of the active tab and set an active class
            this.tabContainer.on( 'easytabs:after', () => {
                // Remove temporary alerts on successful tab switch
                // TODO: Move to Alert service
                AlertService.removeTemporaryAlerts();
            } );

            // Add tab active class on window load
            jQuery( window ).on( 'load updated_checkout', () => {
                this.setActiveTabClass();
            } );

            this.setTabChangeListeners();
            this.setTabButtonListeners();
        } catch ( e ) {
            // eslint-disable-next-line no-console
            console.log( e );

            return false;
        }

        return true;
    }

    /**
     * Returns the current and target tab indexes
     *
     * @returns any
     */
    getCurrentTab() {
        return this.tabContainer.find( '.cfw-panel.active' ).first();
    }

    setTabButtonListeners() {
        jQuery( document.body ).on( 'click', '.cfw-tab-link, .cfw-next-tab, .cfw-prev-tab', ( event ) => {
            const tab = jQuery( event.currentTarget ).data( 'tab' );

            if ( tab ) {
                this.tabContainer.easytabs( 'select', tab );
            }
        } );
    }

    setTabChangeListeners() {
        /**
         * Some gateways really really want to fire their stuff when visible.
         *
         * This provides a special event for our compat classes to listen to
         */
        jQuery( document.body ).on( 'cfw-after-tab-change', ( event, clicked, target ) => {
            // Only run on the payment tab
            if ( jQuery( target ).attr( 'href' ) === `#${TabService.paymentMethodTabId}` ) {
                // Global event for gateways that need special handling
                jQuery( document.body ).trigger( 'cfw-payment-tab-loaded' );
                LoggingService.logEvent( 'Fired cfw-payment-tab-loaded event.' );
            }
        } );

        this.tabContainer.bind( 'easytabs:before', ( event, clicked, target ) => {
        // Fire event that we can listen to around the world
            jQuery( document.body ).trigger( 'cfw-before-tab-change', [ event, clicked, target ] );
            LoggingService.logEvent( 'Fired cfw-before-tab-change event.' );
        } );

        this.tabContainer.bind( 'easytabs:after', ( event, clicked, target ) => {
            // Scroll to top of tab container
            this.scrollToTop();

            // Set current tab active class on form
            this.setActiveTabClass();

            // Fire event that we can listen to around the world
            jQuery( document.body ).trigger( 'cfw-after-tab-change', [ event, clicked, target ] );
            LoggingService.logEvent( 'Fired cfw-after-tab-change event.' );
        } );
    }

    scrollToTop() {
        // Scroll to the top of current tab on tab switch
        jQuery( 'html, body' ).animate( {
            scrollTop: this.tabContainer.offset().top,
        }, 300 );
    }

    /**
     * Set active tab class on form
     */
    setActiveTabClass() {
        const { checkoutForm } = DataService;

        // Add a class to checkout form to indicate payment tab is active tab
        // Doesn't work when tab is initialized by hash in URL
        const currentTabId = this.getCurrentTab().attr( 'id' );
        const activeClass = `${currentTabId}-active`;

        checkoutForm.removeClass( 'cfw-customer-info-active' ).removeClass( 'cfw-shipping-method-active' ).removeClass( 'cfw-payment-method-active' ).addClass( activeClass );
    }

    /**
     * @param {string} tabId
     */
    static go( tabId ): void {
        if ( !Main.instance.tabService.tabBreadcrumbContainer.length ) {
            return;
        }

        try {
            Main.instance.tabService.tabContainer.easytabs( 'select', tabId );
        } catch ( e ) {
            console.log( `Could not select tab: ${e.message}` );
        }
    }

    /**
     * @return {any}
     */
    get tabContainer(): any {
        return this._tabContainer;
    }

    /**
     * @param {any} value
     */
    set tabContainer( value: any ) {
        this._tabContainer = value;
    }

    get tabBreadcrumbContainer(): any {
        return this._tabBreadcrumbContainer;
    }

    set tabBreadcrumbContainer( value: any ) {
        this._tabBreadcrumbContainer = value;
    }

    static get customerInformationTabId(): string {
        return this._customerInformationTabId;
    }

    static get shippingMethodTabId(): string {
        return this._shippingMethodTabId;
    }

    static get paymentMethodTabId(): string {
        return this._paymentMethodTabId;
    }

    static get orderReviewTabId(): string {
        return this._orderReviewTabId;
    }
}

export default TabService;
