<?php

/**
 * This file is used to markup the cart list product item actions.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product/actions.php.
 *
 * Available global vars: $product, $cart_item, $cart_item_key
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="xt_woofc-actions">
    <?php
    echo apply_filters(
        'woocommerce_cart_item_remove_link', '
        <a href="#" class="xt_woofc-delete-item">
            <i class="'.xt_woofc_product_delete_icon_class().'"></i>
            <span>'.esc_html__( 'Remove', 'woo-floating-cart' ).'</span>
        </a>',
        $cart_item_key
    );
    ?>
</div>