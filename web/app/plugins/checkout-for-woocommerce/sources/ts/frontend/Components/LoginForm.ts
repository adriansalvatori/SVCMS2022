import cfwAjaxLogin          from '../../functions/cfwAjaxLogin';
import AccountExistsAction   from '../Actions/AccountExistsAction';
import DataService           from '../Services/DataService';

const debounce = require( 'debounce' );
const Cookies = require( 'js-cookie' );

class LoginForm {
    private readonly _debounceAccountExists;

    public static readonly loginFormModalTriggerId = '#cfw-login-modal-trigger';

    protected accountExistsAction: AccountExistsAction;

    protected lastEmailValue: string;

    constructor() {
        this.accountExistsAction = new AccountExistsAction();
        this._debounceAccountExists = debounce( this.triggerAccountExistsCheck.bind( this ), 200 );
        this.setListeners();
    }

    setListeners(): void {
        if ( DataService.getSetting( 'enable_account_exists_check' ) ) {
            this.setAccountCheckListener();
        }

        const loginForm = jQuery( '#cfw_login_modal_form' );

        this.setLoginModalListener();
        this.setCreateAccountCheckboxListener();

        loginForm.parsley( {
            errorsContainer( parsleyElement ) {
                return parsleyElement.$element.parents( '.cfw-input-wrap' );
            },
        } );

        jQuery( document.body ).on( 'click', '#cfw-login-btn', ( event ) => {
            event.preventDefault();

            if ( !loginForm.parsley().validate() ) {
                return false;
            }

            const username = jQuery( '#cfw_login_username' ).val() as string;
            const password = jQuery( '#cfw_login_password' ).val() as string;

            const allInputs          = jQuery( '#cfw_login_modal_form :input' );
            const otherInputElements = allInputs.not( '#cfw_login_username, #cfw_login_password, #cfw-login-btn' ).toArray() as Array<HTMLInputElement>;
            const namesToValues      = otherInputElements.map( ( input ) => [ input.name, jQuery( input ).val() ] );
            const otherInputs        = Object.fromEntries( namesToValues ) as Record<string, string>;

            cfwAjaxLogin( username, password, otherInputs );
            return false;
        } );
    }

    setLoginModalListener(): void {
        // When billing email changes, sync to login form
        jQuery( document.body ).on( 'change', '#billing_email', () => {
            jQuery( '#cfw_login_username' ).val( jQuery( '#billing_email' ).val() ).trigger( 'change' );
        } );

        // Click listener
        jQuery( LoginForm.loginFormModalTriggerId  ).modaal( {
            content_source: '#cfw_login_modal',
            width: 600,
            before_close: () => {
                if ( DataService.getSetting( 'is_registration_required' ) ) {
                    return;
                }

                // Don't bug user for 7 days if they already opened the modal
                Cookies.set( 'cfw_login_modal_shown', true, { expires: 7 } );
            },
            after_open: () => {
                const username = jQuery( '#cfw_login_username' );
                const emailValue = jQuery( '#billing_email' ).val();

                if ( emailValue !== '' ) {
                    username.val( emailValue );

                    setTimeout( () => {
                        jQuery( '#cfw_login_password' ).get( 0 ).focus();
                    }, 200 );
                }
            },
        } );

        // Account exists listener (once)
        jQuery( document.body ).one( 'cfw_account_exists', () => {
            // If the user already saw the login nag, don't show it again
            if ( Cookies.get( 'cfw_login_modal_shown' ) ) {
                return;
            }

            LoginForm.openModal();
        } );

        jQuery( document.body ).on( 'click', '.showlogin', ( e ) => {
            e.preventDefault();
            LoginForm.openModal();

            return false;
        } );

        jQuery( document.body ).on( 'cfw_account_exists', () => {
            LoginForm.hideCreatePasswordField();
        } );

        jQuery( document.body ).on( 'cfw_account_not_exists', () => {
            if ( DataService.getSetting( 'check_create_account_by_default' ) ) {
                LoginForm.showCreatePasswordField();
            }
        } );

        jQuery( document.body ).on( 'click', '#cfw_login_modal_close', ( e ) => {
            e.preventDefault();

            LoginForm.closeModal();
        } );
    }

    public static openModal(): void {
        jQuery( LoginForm.loginFormModalTriggerId ).modaal( 'open' );
    }

    public static closeModal(): void {
        jQuery( LoginForm.loginFormModalTriggerId ).modaal( 'close' );
    }

    setAccountCheckListener(): void {
        const emailInput: JQuery<HTMLInputElement> = jQuery( '#billing_email' );

        if ( emailInput.length ) {
            // Add check to keyup event
            emailInput.on( 'keyup change', this._debounceAccountExists );

            // Handles page onload use case
            this.triggerAccountExistsCheck();
        }
    }

    triggerAccountExistsCheck(): void {
        const emailInput: JQuery<HTMLInputElement> = jQuery( '#billing_email' );
        const emailValue: string = emailInput.length ? emailInput.val().toString() : '';

        if ( emailValue.length && emailValue !== this.lastEmailValue && !emailInput.hasClass( 'input-hidden' ) ) {
            this.accountExistsAction.load( {
                email: emailValue,
            } );
            this.lastEmailValue = emailValue;
        }
    }

    setCreateAccountCheckboxListener(): void {
        if ( !DataService.getSetting( 'registration_generate_password' ) ) {
            const createAccountCheckbox = jQuery( '#createaccount' );

            createAccountCheckbox.on( 'change', function () {
                if ( jQuery( this ).is( ':checked' ) ) {
                    LoginForm.showCreatePasswordField();
                } else {
                    LoginForm.hideCreatePasswordField();
                }
            } ).trigger( 'change' );
        }
    }

    public static hideCreatePasswordField(): void {
        const accountPassword = jQuery( '#account_password' );
        const accountPasswordSlide = jQuery( '#cfw-account-password-slide' );

        accountPasswordSlide.slideUp( 300 );
        accountPassword.attr( 'data-parsley-required', 'false' );
        accountPassword.prop( 'disabled', true );
    }

    public static showCreatePasswordField(): void {
        const accountPassword = jQuery( '#account_password' );
        const accountPasswordSlide = jQuery( '#cfw-account-password-slide' );

        accountPasswordSlide.slideDown( 300 );
        accountPassword.attr( 'data-parsley-required', 'true' );
        accountPassword.prop( 'disabled', false );
    }
}

export default LoginForm;
