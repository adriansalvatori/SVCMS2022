import FieldValidationRefresher from '../Interfaces/FieldValidationRefresher';
import Main                     from '../Main';
import DataService              from './DataService';
import LoggingService           from './LoggingService';
import jqXHR = JQuery.jqXHR;

// eslint-disable-next-line camelcase
declare let wc_address_i18n_params: any;

const debounce = require( 'debounce' );

class ParsleyService implements FieldValidationRefresher {
    /**
     * @type {any}
     * @private
     */
    private _parsley: any;

    private readonly _debouncedParsleyRefresh;

    static xhrCache: Record<string, jqXHR<any>> = {};

    constructor() {
        this._debouncedParsleyRefresh = debounce( this.refreshParsley, 200 );

        this.setParsleyValidators();
    }

    setParsleyValidators(): void {
        // Init Parsley
        jQuery( window ).on( 'load', () => {
            this.parsley = ( <any>window ).Parsley;
            this.parsley.on( 'form:error', () => {
                jQuery( document.body ).trigger( 'cfw-remove-overlay' );
                LoggingService.logEvent( 'Fired cfw-remove-overlay event.' );
            } );

            try {
                ( <any>window ).Parsley.setLocale( DataService.getSetting( 'parsley_locale' ) );
            } catch {
                const settings = DataService.getSettings();
                LoggingService.logError( `CheckoutWC: Could not load Parsley translation domain (${settings.parsley_locale})` );
            }

            // Attach errors to the outer parent so that select arrow styling isn't effected by dynamic height of cfw-input-wrap
            DataService.checkoutForm.parsley( {
                errorsContainer( parsleyElement ) {
                    return parsleyElement.$element.parents( '.cfw-input-wrap' );
                },
            } );

            ( <any>window ).Parsley.addValidator( 'postcode', {
                validateString( postcode, nonsense, parsleyField: ParsleyField ) {
                    if ( !postcode.length ) {
                        return true;
                    }

                    const { id, placeholder } = <HTMLInputElement>parsleyField.element;

                    if ( !id.includes( '_' ) ) {
                        return true;
                    }

                    const prefix = id.split( '_' ).shift();
                    const countryElement = jQuery( `#${prefix}_country` );

                    const url = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', 'cfw_validate_postcode' );

                    const ajaxOptions = {
                        type: 'POST',
                        url,
                        data: {
                            postcode,
                            country: countryElement.val(),
                        },
                        dataType: 'json',
                        cache: false,
                        statusCode: {
                            202() {
                                LoggingService.log( 'CheckoutWC: Invalid postcode validation request. Must include postcode and country.' );
                            },
                        },
                    };

                    const requestFingerprint = jQuery.param( ajaxOptions );

                    const xhr = ParsleyService.xhrCache[ requestFingerprint ] || jQuery.ajax( ajaxOptions ).fail( ( jqueryXHR ) => {
                        const template = jqueryXHR.responseJSON.message;
                        const message = template
                            .replace( '%s', placeholder.toLowerCase() );

                        ( <any>window ).Parsley.addMessage( 'en', 'postcode', message );
                    } );

                    ParsleyService.xhrCache[ requestFingerprint ] = xhr;

                    return xhr;
                },
            } );

            ( <any>window ).Parsley.addValidator( 'emailDomain', {
                validateString( email ) {
                    if ( !email.length ) {
                        return true;
                    }

                    // If email address is from a common domain, allow it
                    if (
                        email.endsWith( '@gmail.com' )
                        || email.endsWith( '@yahoo.com' )
                        || email.endsWith( '@outlook.com' )
                        || email.endsWith( '@hey.com' )
                        || email.endsWith( '@hotmail.com' )
                    ) {
                        return true;
                    }

                    const url = DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', 'cfw_validate_email_domain' );

                    const ajaxOptions = {
                        type: 'POST',
                        url,
                        data: {
                            email,
                        },
                        dataType: 'json',
                        cache: false,
                    };

                    const requestFingerprint = jQuery.param( ajaxOptions );

                    const xhr = ParsleyService.xhrCache[ requestFingerprint ] ||  jQuery.ajax( ajaxOptions ).fail( ( jqueryXHR ) => {
                        ( <any>window ).Parsley.addMessage( 'en', 'emailDomain', jqueryXHR.responseJSON.message );
                    } );

                    ParsleyService.xhrCache[ requestFingerprint ] = xhr;

                    return xhr;
                },
            } );

            // Don't validate fields that aren't visible but are on the active tab
            ( <any>window ).Parsley.on( 'field:validated', ( fieldInstance ) => {
                if ( fieldInstance.validationResult === true )  {
                    return;
                }

                const activeTab = Main.instance.tabService.getCurrentTab();
                let onActiveTab = false;

                // If one page checkout or the field is on the active tab, set onActiveTab to true
                if ( DataService.getSetting( 'enable_one_page_checkout' ) || !activeTab.length || jQuery.contains( activeTab.get( 0 ), fieldInstance.$element.get( 0 ) ) ) {
                    onActiveTab = true;
                }

                const fieldIsHiddenAndOnActiveTab = ( onActiveTab && fieldInstance.$element.is( ':hidden' ) );
                const fieldContainerIsHidden = fieldInstance.$element.parents( '.form-row' ).hasClass( 'hidden' );
                const fieldIsAHiddenIconicDeliverySlotsField = fieldInstance.$element.parents( '#jckwds-fields' ).css( 'display' ) === 'none';

                if ( fieldContainerIsHidden || fieldIsHiddenAndOnActiveTab || fieldIsAHiddenIconicDeliverySlotsField ) {
                    LoggingService.log( 'Bypassing Parsley validation for field below.', false, fieldInstance.$element );
                    LoggingService.log( `Reason fieldIsHiddenAndOnActiveTab: ${fieldIsHiddenAndOnActiveTab ? 'true' : 'false'}` );
                    LoggingService.log( `Reason fieldContainerIsHidden: ${fieldContainerIsHidden ? 'true' : 'false'}` );
                    LoggingService.log( `Reason fieldIsAHiddenIconicDeliverySlotsField: ${fieldIsAHiddenIconicDeliverySlotsField ? 'true' : 'false'}` );

                    // hide the message wrapper
                    fieldInstance._ui.$errorsWrapper.css( 'display', 'none' );

                    // change the validation result to true
                    fieldInstance.validationResult = true;

                    return;
                }

                LoggingService.log( 'Field did not pass validation. Field object is logged below.', false, fieldInstance.$element );
            } );

            // If we don't call this here, changing the state
            // field to 'Select an option' doesn't fire validation
            ( <any>window ).setTimeout( () => this.queueRefreshParsley() );
        } );

        /**
         * There's a lot going on here, but here's essentially what we're doing.
         *
         * If the state field changes, we check to see what field type it is and do the following:
         * - Add correct classes to field parent wrap
         * - Make any changes to the Parsley validation
         * - Refresh Parsley
         *
         * The fields we are handling in this routine are state, city, and postcode for both address types.
         *
         * Lastly, we do this in a timer because we can't guarantee that WooCommerce will be done making their changes
         * before this runs. So by using setTimeout, we ensure that they are completely done before we do our stuff.
         *
         * We use country_to_state_changing instead of country_to_state_changed because it always fires
         */
        jQuery( document.body ).on( 'country_to_state_changing', ( event, country, wrapper ) => {
            if ( typeof wrapper === 'undefined' ) {
                return;
            }

            ( <any>window ).setTimeout( () => {
                const localeJson = wc_address_i18n_params.locale.replace( /&quot;/g, '"' );
                const locale = JSON.parse( localeJson );

                const { required: stateFieldRequired } = jQuery.extend( true, {}, locale.default.state, ( locale[ country ] ?? {} ).state ?? {} );

                wrapper.find( '#billing_state, #shipping_state' ).each( ( index, stateElement ) => {
                    const stateField = jQuery( stateElement );
                    const stateWrapper = stateField.parents( '.cfw-input-wrap' );

                    if ( stateField.is( 'select' ) ) {
                        stateWrapper.addClass( 'cfw-select-input' );
                        stateField.trigger( 'cfw-after-field-country-to-state-changed' );
                        LoggingService.logEvent( 'Fired cfw-after-field-country-to-state-changed event.' );

                        stateWrapper.addClass( 'cfw-state-input cfw-label-is-floated' ).removeClass( 'cfw-hidden-input cfw-text-input' );
                    } else if ( stateField.attr( 'type' ) === 'text' ) {
                        const stateFieldLabel = stateField.parents( '.cfw-input-wrap' ).find( 'label' ).text().replace( '*', '' );

                        stateField.attr( {
                            placeholder: stateFieldLabel,
                        } );

                        stateElement.dataset.placeholder = stateFieldLabel;

                        stateField.addClass( 'input-text' )
                            .trigger( 'cfw-after-field-country-to-state-changed' );
                        LoggingService.logEvent( 'Fired cfw-after-field-country-to-state-changed event.' );

                        stateWrapper.addClass( 'cfw-text-input cfw-label-is-floated' ).removeClass( 'cfw-hidden-input cfw-select-input cfw-state-select' );
                    } else {
                        stateField.addClass( 'hidden' );

                        stateWrapper.addClass( 'cfw-hidden-input' ).removeClass( 'cfw-text-input cfw-select-input cfw-state-input cfw-label-is-floated' );
                    }

                    // Handle required toggle
                    // We have to add the parsley attributes here because the field is
                    // recreated and thus loses anything that was previously there.
                    const group = stateField.parents( '.cfw-panel.active' ).attr( 'id' );

                    stateField.attr( {
                        'data-parsley-trigger': stateFieldRequired ? 'keyup change focusout' : null,
                        'data-parsley-group': stateFieldRequired ? group : null,
                        'data-parsley-required': stateFieldRequired.toString(),
                    } );
                } );

                this.queueRefreshParsley();
            } );
        } );
    }

    queueRefreshParsley(): void {
        ( this._debouncedParsleyRefresh )();
    }

    refreshParsley() {
        // Re-register all the elements
        DataService.checkoutForm.parsley().refresh();

        LoggingService.logNotice( 'Parsley refreshed.' );
    }

    destroy(): void {
        DataService.checkoutForm.parsley().destroy();
    }

    /**
     * refreshField
     *
     * The input event is what Parsley is listening for.
     *
     * @param {Array<HTMLElement>} elements
     */
    refreshField( ...elements: HTMLElement[] ): void {
        jQuery( elements ).trigger( 'input' );
    }

    /**
     * @returns {any}
     */
    get parsley(): any {
        return this._parsley;
    }

    /**
     * @param value
     */
    set parsley( value: any ) {
        this._parsley = value;
    }
}

export default ParsleyService;
