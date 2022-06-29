import DataService                     from '../Services/DataService';
import Action                          from './Action';

/**
 * Ajax does the account exist action. Takes the information from email box and fires of a request to see if the account
 * exists
 */
class AccountExistsAction extends Action {
    protected accountExistsClass = 'cfw-account-does-exist';

    /**
     * @param email
     */
    constructor() {
        // Call parent
        super( 'account_exists' );
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

        const createAccount =  jQuery( '#createaccount.cfw-create-account-checkbox' ); // only target our specific checkbox in our stuff
        const registerUserCheckbox: any = ( createAccount.length > 0 ) ? createAccount : null;
        const registerContainer: any = jQuery( '#createaccount' ).parent( 'div' );

        // Cleanup any login required alerts
        jQuery( '.cfw-login-required-error' ).remove();

        // If account exists slide down the password field, uncheck the register box, and hide the container for the checkbox
        DataService.setRuntimeParameter( 'runtime_email_matched_user', resp.account_exists );

        jQuery( document.body ).removeClass( this.accountExistsClass );

        // Account exists
        if ( resp.account_exists ) {
            jQuery( document.body ).trigger( 'cfw_account_exists' );
            jQuery( document.body ).addClass( this.accountExistsClass );
        } else {
            jQuery( document.body ).trigger( 'cfw_account_not_exists' );
            jQuery( document.body ).removeClass( this.accountExistsClass );
        }

        if ( registerUserCheckbox && registerUserCheckbox.is( ':checkbox' ) ) {
            registerContainer.css( 'display', resp.account_exists ? 'none' : 'flex' );
            registerUserCheckbox.prop( 'disabled', resp.account_exists );
            registerUserCheckbox.prop( 'checked', !resp.account_exists && DataService.getSetting( 'check_create_account_by_default' ) );
        }
    }
}

export default AccountExistsAction;
