import Alert        from '../frontend/Components/Alert';
import AlertService from '../frontend/Services/AlertService';
import cfwAjax      from './cfwAjax';

function handleError( xhr: JQuery.jqXHR, textStatus: string, errorThrown: string ): void {
    const message = `An error occurred during login. Error: ${errorThrown} (${textStatus})`;

    AlertService.queueAlert( new Alert( 'error', message, 'cfw-alert-error' ), 'login' );
    AlertService.showAlerts( 'login', '#cfw-login-alert-container' );
}

function handleSuccess( rawResponse: unknown ): void {
    const response = rawResponse === 'string' ? JSON.parse( rawResponse ) : rawResponse;

    if ( response?.logged_in ) {
        window.location.reload();
        return;
    }

    const alert = new Alert( 'error', response?.message, 'cfw-alert-error' );
    AlertService.queueAlert( alert, 'login' );
    AlertService.showAlerts( 'login', '#cfw-login-alert-container' );
}

export default function cfwAjaxLogin( email: string, password: string, additionalFields: Record<string, string> ): void {
    const data = {
        email,
        password,
        ...additionalFields,
    };

    const args = {
        type: 'POST',
        data,
        error: handleError,
        success: handleSuccess,
    };

    cfwAjax( 'login', args );
}
