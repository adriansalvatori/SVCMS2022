import DataService                                from './DataService';
import TabService                                 from './TabService';

class OrderReviewStepService {
    constructor() {
        if ( !DataService.getSetting( 'order_review_step_enabled' ) ) {
            return;
        }

        // If submitting fails for any reason, assume a payment issue and go back to the payment tab
        jQuery( document.body ).on( 'cfw_checkout_place_order_event_returned_false cfw_complete_order_failure wc_stripe_submit_error', OrderReviewStepService.goToPaymentTabOnError );
    }

    static goToPaymentTabOnError(): void {
        const termsCheckbox = jQuery( '#terms' );

        if ( termsCheckbox.length && !termsCheckbox.is( ':checked' ) ) {
            return;
        }

        TabService.go( TabService.paymentMethodTabId );
    }
}

export default OrderReviewStepService;
