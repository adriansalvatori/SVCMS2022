<?php

/**
 * This file is used to markup the cart list product item attributes.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product/attributes.php.
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
 * @version     2.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$show_sku        = xt_woofc_option_bool( 'cart_product_show_sku', false );
$show_attributes = xt_woofc_option_bool( 'cart_product_show_attributes', false );

if ( $show_sku ) {
	$sku = $product->get_sku();
}

if ( $show_attributes ) {
	$attributes = xt_woofc_item_attributes( $cart_item );

	ob_start();
    do_action( 'xt_woofc_before_product_attributes', $cart_item, $cart_item_key );
	$before_attributes = ob_get_clean();

    ob_start();
    do_action( 'xt_woofc_after_product_attributes', $cart_item, $cart_item_key );
    $after_attributes = ob_get_clean();

    if(!empty($before_attributes)) {
        $attributes = $before_attributes.$attributes;
    }

    if(!empty($after_attributes)) {
        $attributes = $attributes.$after_attributes;
    }
}
?>

<?php if ( ( $show_attributes && ! empty( $attributes ) ) || ( $show_sku && ! empty( $sku ) ) ): ?>

    <div class="xt_woofc-product-attributes">

        <?php if ( $show_sku && ! empty( $sku ) ): ?>
            <dl class="xt_woofc-sku">
                <dt><?php echo esc_html__( "SKU: ", "woo-floating-cart" ); ?></dt>
                <dd><?php echo esc_html($sku); ?></dd>
            </dl>
		<?php endif; ?>

        <?php
		if ( $show_attributes && ! empty( $attributes ) ) {

            echo wp_kses_post( $attributes );
		}
        ?>

    </div>

<?php endif; ?>