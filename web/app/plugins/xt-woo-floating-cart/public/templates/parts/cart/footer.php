<?php

/**
 * This file is used to markup the cart footer.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/footer.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @link       http://xplodedthemes.com
 * @since      2.6.0
 *
 * @package    XT_Woo_Floating_Cart
 * @subpackage XT_Woo_Floating_Cart/public/templates/parts
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="xt_woofc-footer">

    <a href="<?php echo xt_woofc_checkout_link(); ?>" class="xt_woofc-checkout xt_woofc-btn">
        <em>
            <span class="xt_woofc-footer-label"><?php echo xt_woofc_checkout_label(); ?></span>
            <span class="xt_woofc-dash">-</span>
            <span class="amount"><?php echo xt_woofc_checkout_total(); ?></span>
            <svg class="xt_woofc-checkout-arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="24px" height="24px" viewBox="0 0 24 24" enable-background="new 0 0 24 24" xml:space="preserve"><line fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="3" y1="12" x2="21" y2="12"/><polyline fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" points="15,6 21,12 15,18 "/></svg>
        </em>
    </a>

    <?php xt_woo_floating_cart()->get_template( 'parts/trigger' ); ?>

</div>
