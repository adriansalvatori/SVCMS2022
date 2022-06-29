<?php

/**
 * This file is used to markup the cart list.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list.php.
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $no_container;
?>

<?php if(empty($no_container)): ?>
<div class="xt_woofc-list-wrap">
<?php endif; ?>

	<?php do_action( 'xt_woofc_before_cart_list' ); ?>

    <ul class="xt_woofc-list">
		<?php
		xt_woo_floating_cart()->get_template( 'parts/cart/list/empty' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$product = xt_woofc_item_product( $cart_item, $cart_item_key );

			if ( $product && $product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

				xt_woo_floating_cart()->get_template( 'parts/cart/list/product', array(
					'cart_item_key' => $cart_item_key,
					'cart_item'     => $cart_item,
					'product'       => $product
				) );
			}
		}
		?>
    </ul>

	<?php do_action( 'xt_woofc_after_cart_list' ); ?>

<?php if(empty($no_container)): ?>
</div>
<?php endif; ?>
