<?php

/**
 * This file is used to markup the cart body part of the minicart.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/body.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="xt_woofc-body">

	<?php do_action( 'xt_woofc_before_cart_body_header' ); ?>

	<div class="xt_woofc-body-header"><?php do_action( 'xt_woofc_cart_body_header' ); ?></div>

	<?php do_action( 'xt_woofc_after_cart_body_header' ); ?>

	<?php xt_woo_floating_cart()->get_template( 'parts/cart/list' ); ?>

	<?php do_action( 'xt_woofc_before_cart_body_footer' ); ?>

	<div class="xt_woofc-body-footer"><?php do_action( 'xt_woofc_cart_body_footer' ); ?></div>

	<?php do_action( 'xt_woofc_after_cart_body_footer' ); ?>

</div> <!-- .xt_woofc-body -->
