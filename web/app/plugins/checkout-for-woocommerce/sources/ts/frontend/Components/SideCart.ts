import UpdateSideCart          from '../Actions/UpdateSideCart';
import DataService             from '../Services/DataService';
import CartItemQuantityControl from './CartItemQuantityControl';

class SideCart {
    constructor() {
        this.setTriggers();
    }

    setTriggers(): void {
        const additionalSideCartTriggerSelectors = DataService.getSetting( 'additional_side_cart_trigger_selectors' );

        if ( additionalSideCartTriggerSelectors ) {
            jQuery( document.body ).on( 'click', additionalSideCartTriggerSelectors, this.openCart.bind( this ) );
        }

        jQuery( document.body ).on( 'click', '.cfw-side-cart-open-trigger, .added_to_cart', this.openCart.bind( this ) );
        jQuery( document.body ).on( 'click', '.menu-item a:has(.cfw-side-cart-open-trigger)', this.openCart.bind( this ) );
        jQuery( document.body ).on( 'click', '.cfw-side-cart-close-trigger, .cfw-side-cart-close-btn, #cfw-side-cart-overlay', this.closeCart.bind( this ) );
        jQuery( document.body ).on( 'added_to_cart', () => {
            if ( DataService.getSetting( 'disable_side_cart_auto_open' ) ) {
                return;
            }

            this.openCart();
        } );
        jQuery( document.body ).on( 'click', `a.wc-forward:contains(${DataService.getMessage( 'view_cart' )})`, this.openCart.bind( this ) );
        jQuery( document.body ).on( 'wc_fragments_loaded', this.initializeCart );
        jQuery( document.body ).on( 'click', '.cfw-remove-item-button', this.removeItem.bind( this ) );
        jQuery( document.body ).on( 'cfw_update_cart', this.processCartUpdates.bind( this ) );
        jQuery( document.body ).on( 'change', '.cfw_order_bump_check', this.processCartUpdates.bind( this ) );

        jQuery( document.body ).on( 'click', '#cfw-promo-code-btn', () => {
            const value = jQuery( '#cfw-promo-code' ).val();
            if ( value !== null && value.toString().length !== 0 ) {
                jQuery( document.body ).trigger( 'cfw_update_cart' );
            }
        } );

        jQuery( window ).on( 'load', () => {
            if (
                window.location.hash === '#cfw-cart'
                || (
                    DataService.getRuntimeParameter( 'openCart' )
                    && !DataService.getSetting( 'disable_side_cart_auto_open' )
                )
            ) {
                this.openCart();
            }
        } );

        jQuery( document.body ).on( 'click', '.cfw-show-coupons-module', () => {
            jQuery( '.cfw-promo-wrap' ).slideDown( 300 );
            jQuery( '.cfw-show-coupons-module' ).hide();
        } );
    }

    initializeCart(): void {
        if ( jQuery( '#cfw-side-cart-form' ).hasClass( 'uninitialized' ) ) {
            jQuery( document.body ).trigger( 'wc_fragment_refresh' );
        }
    }

    openCart( e?: Event ): void {
        if ( e ) {
            e.preventDefault();
        }

        jQuery( 'body' ).addClass( 'cfw-side-cart-open' ).removeClass( 'cfw-side-cart-close' );
    }

    closeCart( e?: Event ): void {
        if ( e ) {
            e.preventDefault();
        }
        jQuery( 'body' ).removeClass( 'cfw-side-cart-open' ).addClass( 'cfw-side-cart-close' );
    }

    removeItem( event: Event ): void {
        event.preventDefault();

        const inputElement = jQuery( event.currentTarget ).parents( '.cart-item-row' ).find( '.cfw-edit-item-quantity-value' );

        if ( inputElement ) {
            inputElement.val( 0 );

            CartItemQuantityControl.triggerCartUpdate( inputElement );
        }
    }

    processCartUpdates( event: Event, element?: JQuery ): void {
        let blockedElements = jQuery( '#cfw-side-cart' );

        if ( element ) {
            blockedElements = jQuery( element ).parents( '.cart-item-row' ).find( 'td, th' );
        }

        blockedElements.block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6,
            },
        } );

        new UpdateSideCart( blockedElements ).load( {
            security: DataService.getCheckoutParam( 'update_side_cart_nonce' ),
            cart_data: jQuery( '#cfw-side-cart-form' ).serialize(),
        } );
    }
}

export default SideCart;
