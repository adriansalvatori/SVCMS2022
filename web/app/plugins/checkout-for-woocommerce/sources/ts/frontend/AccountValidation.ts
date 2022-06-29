import Alert        from './Components/Alert';
import AlertService from './Services/AlertService';
import DataService  from './Services/DataService';

class AccountValidation {
    public init(): void {
        jQuery( document.body ).on( 'cfw_account_exists updated_checkout', ( { type } ) => {
            if ( this.validateProvidedAccountEmailAndLoggedInState() ) {
                return;
            }

            this.addAlert();

            if ( type === 'cfw_account_exists' ) {
                AlertService.showAlerts();
            }
        } );
    }

    public getValidatorFactory(): () => Promise<any> {
        return () => new Promise( ( resolve, reject ) => {
            if ( this.validateProvidedAccountEmailAndLoggedInState() ) {
                resolve( true );
                return;
            }

            this.addAlert();
            AlertService.showAlerts();

            reject( new Error( 'CheckoutWC: Account already exists and login required.' ) );
        } );
    }

    protected addAlert(): void {
        const message = DataService.getMessage( 'account_already_registered_notice' );
        const classes = 'cfw-alert-error cfw-login-required-error';

        AlertService.queueAlert( new Alert( 'error', message, classes ) );
    }

    protected validateProvidedAccountEmailAndLoggedInState(): boolean {
        const userIsNotLoggedIn = !DataService.getSetting( 'user_logged_in' );
        const registrationIsRequired = DataService.getSetting( 'is_registration_required' );
        const runtimeEmailMatchedUser = DataService.getRuntimeParameter( 'runtime_email_matched_user' );
        const shouldValidateRequiredRegistration = DataService.getSetting( 'validate_required_registration' );

        return !( userIsNotLoggedIn && registrationIsRequired && runtimeEmailMatchedUser && shouldValidateRequiredRegistration );
    }
}

export default AccountValidation;
