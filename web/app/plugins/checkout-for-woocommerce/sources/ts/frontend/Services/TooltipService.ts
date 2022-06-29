import tippy       from 'tippy.js';
import DataService from './DataService';

class TooltipService {
    constructor() {
        this.setListeners();
    }

    setListeners() {
        jQuery( document.body ).on( 'updated_checkout', this.initCvvTooltips.bind( this ) );
    }

    initCvvTooltips() {
        const cvvTooltipHTML = '<span class="cfw-cvv-tooltip">?</span>';

        // Stripe and Square
        jQuery( '#stripe-cvc-element, #wc-square-credit-card-csc-hosted, #wc-credit-card-form-card-cvc' )
            .parent( '.form-row' )
            .not( '.cfw-has-nonfloating-tooltip' )
            .addClass( 'cfw-has-nonfloating-tooltip' )
            .append( cvvTooltipHTML );

        // SkyVerge Gateways
        jQuery( '.cfw-input-wrap .js-sv-wc-payment-gateway-credit-card-form-csc' )
            .parents( '.form-row' )
            .not( '.cfw-has-tooltip' )
            .addClass( 'cfw-has-tooltip' )
            .append( cvvTooltipHTML );

        tippy( jQuery( '.cfw-cvv-tooltip' ).toArray(), {
            content: DataService.getMessage( 'cvv_tooltip_message' ),
        } );
    }
}

export default TooltipService;
