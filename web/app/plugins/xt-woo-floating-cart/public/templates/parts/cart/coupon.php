<?php

/**
 * This file is used to markup the coupon form.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/coupon.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<form class="xt_woofc-coupon-form" method="post" style="display:none">

    <p><?php esc_html_e( 'If you have a coupon code, please apply it below.', 'woo-floating-cart' ); ?></p>

    <div class="xt_woofc-form-inner">
        <span>
            <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'woo-floating-cart' ); ?>" id="xt_woofc-coupon-code" value=""/>
        </span>
        <span>
            <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woo-floating-cart' ); ?>"><?php esc_html_e( 'Apply coupon', 'woo-floating-cart' ); ?></button>
        </span>
    </div>

    <p class="xt_woofc-coupon-error"></p>

    <?php do_action('xt_woofc_after_coupon_form'); ?>
</form>
