<?php

/**
 * This file is used to markup the cart header.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/header.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     1.8.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="xt_woofc-header">

    <span class="xt_woofc-header-left">
        <span class="xt_woofc-title">
            <?php echo apply_filters( 'xt_woofc_lang_header_title', esc_html__( 'Cart', 'woo-floating-cart' ) ); ?>
        </span>
    </span>

    <span class="xt_woofc-header-right">
        <span class="xt_woofc-notice xt_woofc-notice-na"></span>

        <?php if ( xt_woofc_option_bool( 'enable_coupon_form', false ) ) : ?>
        <span class="xt_woofc-coupon xt_woofc-visible">
            <a class="xt_woofc-show-coupon" href="#"><?php echo apply_filters( 'xt_woofc_lang_header_have_coupon', esc_html__( 'Have a coupon ?', 'woo-floating-cart' ) ); ?></a>
        </span>
        <?php endif; ?>

        <?php if( xt_woofc_option_bool('cart_header_clear_enabled', false)) : ?>
            <span title="<?php echo esc_attr__('Clear Cart', 'woo-floating-cart');?>" class="<?php echo esc_attr(xt_woofc_header_clear_icon_class()); ?>"></span>
        <?php endif; ?>

        <?php if( xt_woofc_option_bool('cart_header_close_enabled', false)) : ?>
        <span title="<?php echo esc_attr__('Close Cart', 'woo-floating-cart');?>" class="<?php echo esc_attr(xt_woofc_header_close_icon_class()); ?>"></span>
        <?php endif; ?>
    </span>

</div>
