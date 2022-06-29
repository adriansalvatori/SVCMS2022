<?php

/**
 * This file is used to markup the coupon list.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/coupon-list.php.
 *
 *  Available global vars:
 *
 * @var $partial
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if(!$partial) {
    $couponsList = '<div class="xt_woofc-coupons">%s</div>';
}else{
    $couponsList = '%s';
}

$sections = '';

ob_start();
if( !empty( WC()->cart->get_coupons() ) ): ?>
    <span class="xt_woofc-coupons-label"><?php echo esc_html__( 'Applied Coupons', 'woo-floating-cart' ); ?></span>
    <div class="xt_woofc-coupons-section">
        <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ): ?>
            <div class="xt_woofc-coupon-item">
                <div class="xt_woofc-coupon-item-inner">
                    <span class="xt_woofc-coupon-code" title="<?php echo esc_attr(strtoupper($code)); ?>"><?php echo strtoupper($code); ?></span>
                    <span class="xt_woofc-coupon-off"><?php echo esc_html__( 'Saved', 'woo-floating-cart' ). ' '. wc_price( WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax ) ) ?></span>
                    <a href="#" class="xt_woofc-remove-coupon" data-coupon="<?php echo esc_attr($code); ?>"><?php _e( '[Remove]', 'woo-floating-cart' ) ?></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif;
$sections = ob_get_clean();

foreach (  xt_woo_floating_cart()->frontend()->get_coupons() as $section => $coupons ){

    if( empty( $coupons ) ) {
        continue;
    }

    $sectionContainer = '<div class="xt_woofc-coupons-section xt_woofc-coupons-section-%1$s">%2$s</div>';

    $label 	= sprintf( '<span class="xt_woofc-coupons-label">%s</span>', $section === "valid" ? esc_html__( 'Available Coupons', 'woo-floating-cart' ) : esc_html__( 'Unavailable Coupons', 'woo-floating-cart' ) );

    $rows = '';

    ob_start();

    foreach ( $coupons as $coupon_data ) {

        $coupon = $coupon_data['coupon'];
        ?>
        <div class="xt_woofc-coupon-item">
            <div class="xt_woofc-coupon-item-inner">
                <span class="xt_woofc-coupon-code" title="<?php echo esc_attr(strtoupper($coupon->get_code())); ?>"><?php echo strtoupper($coupon->get_code()); ?></span>
                <span class="xt_woofc-coupon-off"><?php printf( __( 'Get %s off', 'woo-floating-cart' ), $coupon_data['off_value'] )  ?></span>
                <?php if(!empty($coupon->get_description())): ?>
                    <span class="xt_woofc-coupon-desc"><?php echo esc_html($coupon->get_description()) ?></span>
                <?php endif; ?>
                <?php if( $section === 'valid' ): ?>
                    <a href="#" class="xt_woofc-coupon-apply button" data-coupon="<?php echo esc_attr($coupon->get_code()) ?>"><?php _e( 'Apply Coupon', 'woo-floating-cart' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    $rows .= ob_get_clean();

    $sections .= $label.sprintf( $sectionContainer, $section, $rows );

}

printf( $couponsList, $sections );
