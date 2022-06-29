import Main                  from '../../Main';
import Compatibility         from '../Compatibility';

declare let wc_gc_params: any;

class WooCommerceGiftCards extends Compatibility {
    constructor() {
        super( 'WooCommerceGiftCards' );
    }

    load(): void {
        jQuery( document.body ).on( 'click', '#wc_gc_cart_redeem_send', ( e ) => {
            e.preventDefault();

            const code = jQuery( '#wc_gc_cart_code' ).val();
            if ( code ) {
                Main.instance.updateCheckoutService.triggerUpdateCheckout();
            }
        } );

        /**
         * Remove gift card from checkout buttons.
         */
        jQuery( document.body ).on( 'click', '.wc_gc_remove_gift_card', function ( e ) {
            e.preventDefault();

            const $el = jQuery( this );
            const cardId = $el.data( 'giftcard' );

            jQuery.ajax( {
                type: 'post',
                url: wc_gc_params.wc_ajax_url.toString().replace(
                    '%%endpoint%%',
                    'remove_gift_card_from_session',
                ),
                data: `wc_gc_cart_id=${cardId}&security=${wc_gc_params.security_remove_card_nonce}`,
                dataType: 'html',
                success() {
                    Main.instance.updateCheckoutService.triggerUpdateCheckout();
                },
            } );

            return false;
        } );
    }
}

export default WooCommerceGiftCards;
