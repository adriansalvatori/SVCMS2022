<?php
/**
 * This file is used to markup the cart list product item quantity input container.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product/quantity.php.
 *
 * Available global vars:
 *
 * @var $product WC_Product
 * @var $cart_item
 * @var $cart_item_key
 * @var $is_bundle_item
 * @var $is_composite_item
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.4.7
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$quantityFormEnabled = xt_woofc_option_bool( 'cart_product_qty_enabled', true );
$quantityFormTemplate = xt_woofc_option( 'cart_product_qty_template', ['input', 'minus', 'plus'] );

if($quantityFormEnabled && !empty($quantityFormTemplate) && !$is_bundle_item) {

    $args = array(
        'input_name'   => "cart[{$cart_item_key}][qty]",
        'input_value'  => $cart_item['quantity'],
        'input_width'  => (25 * ( xt_woofc_digits_count( intval( $cart_item['quantity'] ) ) / 2 ) . 'px'),
        'min_value'    => $product->get_min_purchase_quantity(),
        'max_value'    => $product->get_max_purchase_quantity(),
        'product_name' => $product->get_name(),
    );

    $product_quantity = xt_woofc_quantity_input($args, $product, $cart_item_key, $cart_item);
    $is_hidden = empty($product_quantity) || strpos($product_quantity, 'type="hidden"') !== false || is_numeric($product_quantity);
    $product_quantity = is_numeric($product_quantity) ? 'x '.$product_quantity : $product_quantity;

    $parts = array();
    $output = '';

    if(!$is_hidden) {
        $parts['input'] = '<span class="xt_woofc-quantity-col xt_woofc-quantity-col-input">'.$product_quantity.'</span>';
        $parts['minus'] = '<span class="xt_woofc-quantity-col xt_woofc-quantity-col-minus xt_woofc-quantity-button"><i class="xt_woofcicon-flat-minus"></i></span>';
        $parts['plus'] = '<span class="xt_woofc-quantity-col xt_woofc-quantity-col-plus xt_woofc-quantity-button"><i class="xt_woofcicon-flat-plus"></i></span>';
    }else{
        $parts['input'] = '<span class="xt_woofc-quantity-col xt_woofc-quantity-col-input xt_woofc-quantity-hidden">'.$product_quantity.'</span>';
    }

    foreach ( $quantityFormTemplate as $part_id ) {

        if(isset($parts[ $part_id ])) {
            $output .= $parts[$part_id];
        }
    }

    if(!empty($output)) {

        if(!$is_hidden) {
            ?>
            <div class="xt_woofc-quantity">
                <form>
                    <div class="xt_woofc-quantity-row">
                        <?php echo wp_kses_post($output);?>
                    </div>
                </form>
            </div>
            <?php
        }else{
            ?>
            <div class="xt_woofc-quantity">
                <?php echo wp_kses_post($output);?>
            </div>
            <?php
        }
    }
}
