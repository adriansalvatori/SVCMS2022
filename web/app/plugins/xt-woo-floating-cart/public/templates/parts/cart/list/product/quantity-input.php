<?php
/**
 * This file is used to markup the cart list product item quantity input.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product/quantity-input.php.
 *
 * Available global vars:
 *
 * @var $input_id
 * @var $min_value
 * @var $max_value
 * @var $step
 * @var $input_name
 * @var $label
 * @var $inputmode
 * @var $input_width
 * @var $input_value
 * @var $placeholder
 * @var $classes
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.0.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( $max_value && $min_value === $max_value ) {
    ?>
    <input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>" />
    <?php
} else {
    /* translators: %s: Quantity. */
    $label = ! empty( $args['product_name'] ) ? sprintf( esc_html__( '%s quantity', 'woocommerce' ), wp_strip_all_tags( $args['product_name'] ) ) : esc_html__( 'Quantity', 'woocommerce' );
    ?>
    <?php do_action( 'xt_woofc_before_quantity_input_field' ); ?>
    <input
            style="width: <?php echo esc_attr($input_width); ?>"
            type="number"
            id="<?php echo esc_attr( $input_id ); ?>"
            class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
            step="<?php echo esc_attr( $step ); ?>"
            min="<?php echo esc_attr( $min_value ); ?>"
            max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
            name="<?php echo esc_attr( $input_name ); ?>"
            value="<?php echo esc_attr( $input_value ); ?>"
            title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
            size="4"
            placeholder="<?php echo esc_attr( $placeholder ); ?>"
            inputmode="<?php echo esc_attr( $inputmode ); ?>" />
    <?php do_action( 'xt_woofc_after_quantity_input_field' ); ?>
    <?php
}
