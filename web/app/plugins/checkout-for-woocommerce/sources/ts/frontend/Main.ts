import cfwValidateShippingTab                from '../functions/cfwValidateShippingTab';
import AccountValidation                     from './AccountValidation';
import AmazonPayLegacy                       from './Compatibility/Gateways/AmazonPayLegacy';
import AmazonPayV1                           from './Compatibility/Gateways/AmazonPayV1';
import AmazonPay                             from './Compatibility/Gateways/AmazonPay';
import Braintree                             from './Compatibility/Gateways/Braintree';
import KlarnaCheckout                        from './Compatibility/Gateways/KlarnaCheckout';
import KlarnaPayments                        from './Compatibility/Gateways/KlarnaPayments';
import PayPalForWooCommerce                  from './Compatibility/Gateways/PayPalForWooCommerce';
import PayPalPlusCw                          from './Compatibility/Gateways/PayPalPlusCw';
import Square                                from './Compatibility/Gateways/Square';
import Stripe                                from './Compatibility/Gateways/Stripe';
import WooCommercePensoPay                   from './Compatibility/Gateways/WooCommercePensoPay';
import WooSquarePro                          from './Compatibility/Gateways/WooSquarePro';
import CO2OK                                 from './Compatibility/Plugins/CO2OK';
import EUVatNumber                           from './Compatibility/Plugins/EUVatNumber';
import ExtraCheckoutFieldsBrazil             from './Compatibility/Plugins/ExtraCheckoutFieldsBrazil';
import MondialRelay                          from './Compatibility/Plugins/MondialRelay';
import MyShipper                             from './Compatibility/Plugins/MyShipper';
import NIFPortugal                           from './Compatibility/Plugins/NIFPortugal';
import NLPostcodeChecker                     from './Compatibility/Plugins/NLPostcodeChecker';
import OrderDeliveryDate                     from './Compatibility/Plugins/OrderDeliveryDate';
import PortugalVaspKios                      from './Compatibility/Plugins/PortugalVaspKios';
import PostNL                                from './Compatibility/Plugins/PostNL';
import SendCloud                             from './Compatibility/Plugins/SendCloud';
import ShipMondo                             from './Compatibility/Plugins/ShipMondo';
import WCPont                                from './Compatibility/Plugins/WCPont';
import WooCommerceAddressValidation          from './Compatibility/Plugins/WooCommerceAddressValidation';
import WooCommerceGermanized                 from './Compatibility/Plugins/WooCommerceGermanized';
import WooCommerceGiftCards                  from './Compatibility/Plugins/WooCommerceGiftCards';
import WooFunnelsOrderBumps                  from './Compatibility/Plugins/WooFunnelsOrderBumps';
import Accordion                             from './Components/Accordion';
import AddableFields                         from './Components/AddableFields';
import CartItemQuantityControl               from './Components/CartItemQuantityControl';
import Coupons                               from './Components/Coupons';
import DiscreetAddressLine1Fields            from './Components/DiscreetAddressLine1Fields';
import FormField                             from './Components/FormField';
import Form                                  from './Components/Form';
import FullName                              from './Components/FullName';
import LoginForm                             from './Components/LoginForm';
import LostPasswordModal                     from './Components/LostPasswordModal';
import OrderBump                             from './Components/OrderBump';
import PaymentRequestButtons                 from './Components/PaymentRequestButtons';
import TermsAndConditions                    from './Components/TermsAndConditions';
import AddressInternationalizationService    from './Services/AddressInternationalizationService';
import AlertService                          from './Services/AlertService';
import BillingAddressSyncService             from './Services/BillingAddressSyncService';
import ChromeAutocompleteBugService          from './Services/ChromeAutocompleteBugService';
import CompleteOrderService                  from './Services/CompleteOrderService';
import DataService                           from './Services/DataService';
import FetchifyAddressAutocompleteService    from './Services/FetchifyAddressAutocompleteService';
import FieldPersistenceService               from './Services/FieldPersistenceService';
import GoogleAddressAutocompleteService      from './Services/GoogleAddressAutocompleteService';
import InternationalPhoneFieldService        from './Services/InternationalPhoneFieldService';
import LoggingService                        from './Services/LoggingService';
import OrderReviewStepService                from './Services/OrderReviewStepService';
import ParsleyService                        from './Services/ParsleyService';
import PaymentGatewaysService                from './Services/PaymentGatewaysService';
import SmartyStreetsAddressValidationService from './Services/SmartyStreetsAddressValidationService';
import TabService                            from './Services/TabService';
import TooltipService                        from './Services/TooltipService';
import UpdateCheckoutService                 from './Services/UpdateCheckoutService';
import ValidationService                     from './Services/ValidationService';
import ZipAutocompleteService                from './Services/ZipAutocompleteService';
import BraintreeForWooCommerce from "./Compatibility/Gateways/BraintreeForWooCommerce";

/**
 * The main class of the front end checkout system
 *
 * @deprecated
 */
class Main {
    /**
     * @type {any}
     * @private
     */
    private _tabContainer: any;

    /**
     * @type {any}
     * @private
     */
    private _alertContainer: any;

    /**
     * @type {any}
     * @private
     */
    private _settings: any;

    /**
     * @type {TabService}
     * @private
     */
    private _tabService: TabService;

    /**
     * @type {UpdateCheckoutService}
     * @private
     */
    private _updateCheckoutService: UpdateCheckoutService;

    /**
     * @type {PaymentGatewaysService}
     * @private
     */
    private _paymentGatewaysService: PaymentGatewaysService;

    /**
     * @type boolean
     * @private
     */
    private _loadTabs: any;

    /**
     * @type {Main}
     * @private
     * @static
     */
    private static _instance: Main;

    /**
     * @type {ParsleyService}
     * @private
     */
    private _parsleyService: ParsleyService;

    /**
     * @param {any} checkoutFormElement
     * @param {any} alertContainer
     * @param {any} tabContainerElement
     * @param {any} breadCrumbElement
     * @param {AjaxInfo} ajaxInfo
     * @param {any} settings
     */
    constructor( checkoutFormElement: any, alertContainer: any, tabContainerElement, breadCrumbElement, settings: any ) {
        if ( !Main._instance ) {
            Main._instance = this;
        }

        this.tabContainer = tabContainerElement;
        this.alertContainer = alertContainer;
        this.settings = settings;
        this.loadTabs = this.settings.load_tabs;
        this.tabService = new TabService( this.tabContainer, breadCrumbElement );
        let tabsLoaded = false;

        /**
         * Services
         */
        if ( this.loadTabs ) {
            tabsLoaded = this.tabService.maybeLoadTabs();
        }

        if ( tabsLoaded ) {
            // Only relevant on tabbed pages
            new ChromeAutocompleteBugService();
        }

        const hasDiscreetAddress1Fields = DataService.getSetting( 'enable_discreet_address_1_fields' );

        // Setup the validation service - has to happen after tabs are setup
        new AddressInternationalizationService();

        const accountValidation = new AccountValidation();
        accountValidation.init();

        const validationService = new ValidationService();
        validationService.addValidatorFactory( 'cfw-customer-info', accountValidation.getValidatorFactory() );
        validationService.addValidatorFactory( 'cfw-shipping-method', cfwValidateShippingTab );

        new BillingAddressSyncService();
        new FieldPersistenceService( checkoutFormElement );
        this.parsleyService = new ParsleyService();
        new ZipAutocompleteService( this.parsleyService );

        if ( !hasDiscreetAddress1Fields ) {
            new GoogleAddressAutocompleteService( this.parsleyService );
        }

        if ( DataService.getSetting( 'enable_fetchify_address_autocomplete' ) ) {
            new FetchifyAddressAutocompleteService( this.parsleyService );
        }

        if ( DataService.getSetting( 'enable_smartystreets_integration' ) ) {
            new SmartyStreetsAddressValidationService( DataService.getSetting( 'needs_shipping_address' ) ? 'shipping_' : 'billing_' );
        }

        new CompleteOrderService();
        this.paymentGatewaysService = new PaymentGatewaysService();
        this.updateCheckoutService = new UpdateCheckoutService();
        new AlertService( this.alertContainer );
        new TooltipService();
        new OrderReviewStepService();
        new InternationalPhoneFieldService();

        /**
         * Components
         */
        new Form();
        new Accordion();
        new LoginForm();
        new LostPasswordModal();
        new FormField();
        new Coupons();
        new TermsAndConditions();
        new PaymentRequestButtons();
        new CartItemQuantityControl();
        new OrderBump();
        new AddableFields();
        new FullName();

        if ( hasDiscreetAddress1Fields ) {
            new DiscreetAddressLine1Fields().setupListeners();
        }

        this.loadCompatibilityClasses();

        // Init checkout ( WooCommerce native event )
        jQuery( document.body ).trigger( 'init_checkout' );
        LoggingService.logEvent( 'Fired init_checkout event.' );

        jQuery( document.body ).on( 'cfw-remove-overlay', () => {
            DataService.checkoutForm.unblock();
        } );

        jQuery( document.body ).on( 'cfw_update_cart', () => {
            jQuery( '[name="cfw_update_cart"]' ).val( 'true' );
            Main.instance.updateCheckoutService.queueUpdateCheckout();
        } );

        jQuery( '#cfw-mobile-cart-header' ).on( 'click', ( e ) => {
            e.preventDefault();
            jQuery( '#cfw-cart-summary-content' ).slideToggle( 300 );
            jQuery( '#cfw-expand-cart' ).toggleClass( 'active' );
        } );

        jQuery( window ).on( 'load', () => {
            // Remove the animation blocker
            jQuery( document.body ).removeClass( 'cfw-preload' );

            jQuery( '#wpadminbar' ).appendTo( 'html' );

            // Give plugins a chance to react to our hidden, invisible shim checkbox
            jQuery( '#ship-to-different-address-checkbox' ).trigger( 'change' );

            // Don't blow away pre-existing alerts on the first update checkout call
            AlertService.preserveAlerts = true;
        } );
    }

    /**
     * Load contextually relevant compatibility classes
     */
    private loadCompatibilityClasses(): void {
        [
            new AmazonPay(),
            new AmazonPayLegacy(),
            new AmazonPayV1(),
            new Braintree(),
            new BraintreeForWooCommerce(),
            new CO2OK(),
            new EUVatNumber(),
            new KlarnaCheckout(),
            new KlarnaPayments(),
            new MondialRelay(),
            new NIFPortugal(),
            new NLPostcodeChecker(),
            new OrderDeliveryDate(),
            new PayPalForWooCommerce(),
            new PayPalPlusCw(),
            new PortugalVaspKios(),
            new PostNL(),
            new SendCloud(),
            new ShipMondo(),
            new Square(),
            new Stripe(),
            new WCPont(),
            new WooCommerceAddressValidation(),
            new WooCommerceGermanized(),
            new WooCommerceGiftCards(),
            new WooFunnelsOrderBumps(),
            new WooSquarePro(),
            new WooCommercePensoPay(),
            new MyShipper(),
            new ExtraCheckoutFieldsBrazil(),
        ].forEach( ( compat ) => compat.maybeLoad() );
    }

    /**
     * @returns {TabContainer}
     */
    get tabContainer() {
        return this._tabContainer;
    }

    /**
     * @return {any}
     */
    get alertContainer(): any {
        return this._alertContainer;
    }

    /**
     * @param {any} value
     */
    set alertContainer( value: any ) {
        this._alertContainer = value;
    }

    /**
     * @param value
     */
    set tabContainer( value: any ) {
        this._tabContainer = value;
    }

    /**
     * @returns {any}
     */
    get settings(): any {
        return this._settings;
    }

    /**
     * @param value
     */
    set settings( value: any ) {
        this._settings = value;
    }

    /**
     * @returns {TabService}
     */
    get tabService(): TabService {
        return this._tabService;
    }

    /**
     * @param {TabService} value
     */
    set tabService( value: TabService ) {
        this._tabService = value;
    }

    /**
     * @returns {UpdateCheckoutService}
     */
    get updateCheckoutService(): UpdateCheckoutService {
        return this._updateCheckoutService;
    }

    /**
     * @param {UpdateCheckoutService} value
     */
    set updateCheckoutService( value: UpdateCheckoutService ) {
        this._updateCheckoutService = value;
    }

    /**
     * @returns {PaymentGatewaysService}
     */
    get paymentGatewaysService(): PaymentGatewaysService {
        return this._paymentGatewaysService;
    }

    /**
     * @param {PaymentGatewaysService} value
     */
    set paymentGatewaysService( value: PaymentGatewaysService ) {
        this._paymentGatewaysService = value;
    }

    /**
     * @returns {ParsleyService}
     */
    get parsleyService(): ParsleyService {
        return this._parsleyService;
    }

    /**
     * @param {ParsleyService} value
     */
    set parsleyService( value: ParsleyService ) {
        this._parsleyService = value;
    }

    get loadTabs(): any {
        return this._loadTabs;
    }

    set loadTabs( value: any ) {
        this._loadTabs = value;
    }

    /**
     * @returns {Main}
     */
    static get instance(): Main {
        return Main._instance;
    }
}

export default Main;
