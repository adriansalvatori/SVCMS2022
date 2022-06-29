import ApplyCouponAction     from '../Actions/ApplyCouponAction';
import RemoveCouponAction    from '../Actions/RemoveCouponAction';
import DataService           from '../Services/DataService';

class Coupons {
    constructor() {
        this.setShowCouponsModuleListener();
        this.setApplyCouponListener();
        this.setRemoveCouponListener();
        this.setApplyCouponMobileListener();
    }

    setShowCouponsModuleListener(): void {
        jQuery( document.body ).on( 'click', '.cfw-show-coupons-module', () => {
            jQuery( '.cfw-promo-wrap' ).slideDown( 300 );
            jQuery( '.cfw-show-coupons-module' ).hide();
        } );
    }

    /**
     *
     */
    setApplyCouponListener(): void {
        const promoApplyButton = jQuery( '#cfw-promo-code-btn' );

        jQuery( '#cfw-promo-code' ).on( 'keydown', ( e ) => {
            if ( e.which == 13 ) {
                e.preventDefault();

                promoApplyButton.trigger( 'click' );
            }
        } );

        promoApplyButton.on( 'click', () => {
            const couponField: any = jQuery( '#cfw-promo-code' );

            if ( couponField.val() !== '' ) {
                new ApplyCouponAction( couponField.val() ).load( {
                    security: DataService.getCheckoutParam( 'apply_coupon_nonce' ),
                    coupon_code: couponField.val(),
                } );
                couponField.val( '' ).blur();
            }
        } );
    }

    setRemoveCouponListener(): void {
        jQuery( document.body ).on( 'click', '.woocommerce-remove-coupon', function ( e ) {
            e.preventDefault();
            new RemoveCouponAction().load( {
                security: DataService.getCheckoutParam( 'remove_coupon_nonce' ),
                coupon_code: jQuery( this ).data( 'coupon' ),
            } );
        } );
    }

    setApplyCouponMobileListener(): void {
        const promoApplyButton = jQuery( '#cfw-promo-code-btn-mobile' );

        jQuery( '#cfw-promo-code-mobile' ).on( 'keydown', ( e ) => {
            if ( e.which == 13 ) {
                e.preventDefault();

                promoApplyButton.trigger( 'click' );
            }
        } );

        promoApplyButton.on( 'click', () => {
            const couponField: any = jQuery( '#cfw-promo-code-mobile' );

            if ( couponField.val() !== '' ) {
                new ApplyCouponAction( couponField.val() ).load( {
                    security: DataService.getCheckoutParam( 'apply_coupon_nonce' ),
                    coupon_code: couponField.val(),
                } );
                couponField.val( '' ).blur();
            }
        } );
    }
}

export default Coupons;
