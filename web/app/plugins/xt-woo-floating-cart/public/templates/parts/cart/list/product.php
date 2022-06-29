<?php
/**
 * This file is used to markup the cart list product item.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/cart/list/product.php.
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
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$product_id      = $cart_item['product_id'];
$is_variable     = xt_woofc_item_attributes( $cart_item );
$show_sku        = xt_woofc_option_bool( 'cart_product_show_sku', false );
$show_attributes = xt_woofc_option_bool( 'cart_product_show_attributes', false );

$classes   = array();
$classes[] = 'xt_woofc-product';
$classes[] = 'xt_woofc-' . $product->get_type();

$attributes = array();

if ( ! empty( $is_variable ) ) {
	$classes[] = 'xt_woofc-variable-product';
}

$hide_product = false;

$bundled_product = function_exists( 'wc_pb_is_bundled_cart_item' ) && wc_pb_is_bundled_cart_item( $cart_item );

if ( $bundled_product ) {

	$classes[] = 'xt_woofc-bundled-item';

	$show_bundled_products = xt_woofc_option_bool( 'cart_product_show_bundled_products', true );

	if ( ! $show_bundled_products ) {
		$classes[] = 'xt_woofc-hide';
	}

	if ( ! empty( $cart_item['bundled_by'] ) ) {

		$bundled_by_item = xt_woofc_get_cart_item( $cart_item['bundled_by'] );
		if ( ! empty( $bundled_by_item ) ) {

			if ( ! empty( $bundled_by_item['composite_parent'] ) ) {

				$composite_product = true;

				$attributes['data-group'] = $bundled_by_item['composite_parent'];
				$classes[]  = 'xt_woofc-composite-item';

				$show_composite_product = xt_woofc_option_bool( 'cart_product_show_composite_products', true );

				if ( ! $show_composite_product ) {
					$classes[] = 'xt_woofc-hide';
				}

			} else {

                $attributes['data-group'] = $cart_item['bundled_by'];
			}

		}
	}

}

$composite_product = !empty( $cart_item['composite_parent'] );
if ( $composite_product ) {
	$classes[]         = 'xt_woofc-composite-item';
    $attributes['data-group'] = $cart_item['composite_parent'];

	$show_composite_product = xt_woofc_option_bool( 'cart_product_show_composite_products', true );

	if ( ! $show_composite_product ) {
		$classes[] = 'xt_woofc-hide';
	}
}

if ( $show_sku || $show_attributes ) {
	$classes[] = 'xt_woofc-show-attributes';
}

$classes = apply_filters('xt_woofc_cart_item_classes', $classes);
$classes = implode( ' ', $classes );

$vars = array(
	'product'       => $product,
	'cart_item'     => $cart_item,
	'cart_item_key' => $cart_item_key,
    'is_bundle_item'     => $bundled_product,
    'is_composite_item'  => $composite_product
);
?>

<?php do_action( 'xt_woofc_before_product', $cart_item, $cart_item_key ); ?>

<li class="<?php echo esc_attr( $classes ); ?>"
    data-key="<?php echo esc_attr( $cart_item_key ); ?>"
    data-id="<?php echo esc_attr( $product_id ); ?>"
	<?php foreach ($attributes as $key => $val):?>
        <?php echo esc_attr($key)?>=<?php echo esc_attr($val);?>
    <?php endforeach;?>
>

    <div class="xt_woofc-product-wrap">

        <div class="xt_woofc-product-image">
			<?php
			xt_woo_floating_cart()->get_template( 'parts/cart/list/product/thumbnail', $vars );
			?>
        </div>

        <div class="xt_woofc-product-details">

            <div class="xt_woofc-product-header">
	            <?php
	            xt_woo_floating_cart()->get_template( 'parts/cart/list/product/title', $vars );
	            xt_woo_floating_cart()->get_template( 'parts/cart/list/product/price', $vars );
	            ?>
            </div>

            <div class="xt_woofc-product-body">
                <?php
                do_action( 'xt_woofc_before_product_body', $cart_item, $cart_item_key );

                // Product Attributes
                xt_woo_floating_cart()->get_template( 'parts/cart/list/product/attributes', $vars );

                // Backorder notification.
                if ( $product->backorders_require_notification() && $product->is_on_backorder( $cart_item['quantity'] ) ) {
                    echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="xt_woofc-backorder-notification">' . esc_html__( 'Available on backorder', 'woo-floating-cart' ) . '</p>', $product->get_id() ) );
                }

                do_action( 'xt_woofc_after_product_body', $cart_item, $cart_item_key );
                ?>
            </div>

            <div class="xt_woofc-product-footer">
	            <?php
	            xt_woo_floating_cart()->get_template( 'parts/cart/list/product/quantity', $vars );
	            if ( ! $bundled_product && ! $composite_product ) {
		            xt_woo_floating_cart()->get_template( 'parts/cart/list/product/actions', $vars );
	            }
	            ?>
            </div>
        </div>

    </div>
</li>

<?php do_action( 'xt_woofc_after_product', $cart_item, $cart_item_key ); ?>

