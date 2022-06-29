<?php

/**
 * This file is used to markup the mini-cart.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/trigger.php.
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

$total_items = WC()->cart->get_cart_contents_count();
$icon_type   = xt_woofc_option( 'trigger_icon_type', 'image' );
?>

<a href="#" class="xt_woofc-trigger<?php echo( ( $total_items > 99 ) ? ' xt_woofc-count-big' : '' ); ?> xt_woofc-icontype-<?php echo esc_attr($icon_type); ?>">

    <span class="<?php echo esc_attr(xt_woofc_trigger_cart_icon_class()); ?>"></span>

    <ul class="xt_woofc-count"> <!-- cart items count -->
        <li><?php echo( $total_items ); ?></li>
        <li><?php echo( $total_items + 1 ); ?></li>
    </ul> <!-- .count -->

    <span class="<?php echo esc_attr(xt_woofc_trigger_close_icon_class()); ?>"></span>

</a>