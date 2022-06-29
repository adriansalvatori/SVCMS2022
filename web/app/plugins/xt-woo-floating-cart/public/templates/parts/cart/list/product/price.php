<?php

/**
 * This file is used to markup the cart list product item price.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product/price.php.
 *
 * Available global vars:
 *
 * @var $product WC_Product
 * @var $cart_item
 * @var $cart_item_key
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

<span class="xt_woofc-price amount">
	<?php echo xt_woofc_item_price( $product, $cart_item, $cart_item_key ); ?>
</span>
