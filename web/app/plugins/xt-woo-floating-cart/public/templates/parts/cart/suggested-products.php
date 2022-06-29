<?php

/**
 * This file is used to markup the suggested products.
 *
 * This template can be overridden by copying it to yourtheme/xt-woo-floating-cart/parts/suggested-products.php.
 *
 * Available global vars:
 *
 * @var $display_type
 * @var $items_count
 * @var $exclude_ids
 * @var $suggested_products
 * @var $title
 * @var $hide_add_to_cart_button
 *
 * HOWEVER, on occasion we will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see         https://docs.xplodedthemes.com/article/127-template-structure
 * @author 		XplodedThemes
 * @package     XT_Woo_Floating_Cart/Templates
 * @version     2.4.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$args = array(
    'post_type'            	=> array('product'),
    'post_status'    		=> 'publish',
    'ignore_sticky_posts'  	=> 1,
    'no_found_rows'       	=> 1,
    'posts_per_page'       	=> $items_count,
    'post__not_in'        	=> $exclude_ids,
    'post__in'        	    => $suggested_products,
    'orderby'             	=> 'rand',
    'meta_query'			=> array(
        array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '=',
        )
    )
);

$products = new WP_Query( $args );

$classes = array('xt_woofc-sp', 'xt_woofc-sp-'.$display_type);

if(!$products->have_posts() || empty($suggested_products)) {
	$classes[] = 'xt_woofc-sp-empty';
}

$classes = implode(" ", $classes);
?>

<div class="<?php echo esc_attr($classes); ?>">

    <?php if ( !empty($suggested_products) && $products->have_posts() ) : ?>
    <span class="xt_woofc-sp-title"><?php echo esc_html($title); ?></span>
    <ul class="xt_woofc-sp-products">
        <?php
        while ( $products->have_posts() ) : $products->the_post();
            /* @var $product WC_Product */
            global $product;
            $product_link = $product->get_permalink();
            ?>
            <li class="xt_woofc-sp-item lslide">

                <div class="xt_woofc-sp-item-wrap">
                    <div class="xt_woofc-sp-left-area xt_woofc-product-image">
                        <?php
                        woocommerce_template_loop_product_link_open();
                        woocommerce_template_loop_product_thumbnail();
                        woocommerce_template_loop_product_link_close();
                        ?>
                    </div>

                    <div class="xt_woofc-sp-right-area">
                        <div class="xt_woofc-sp-product-title">
                            <?php
                            woocommerce_template_loop_product_link_open();
                            echo get_the_title();
                            woocommerce_template_loop_product_link_close();
                            ?>
                        </div>
                        <?php woocommerce_template_loop_price(); ?>

                        <?php if(empty($hide_add_to_cart_button)): ?>
                        <?php woocommerce_template_loop_add_to_cart(); ?>
                        <?php endif; ?>
                    </div>
                </div>

            </li>
        <?php endwhile; wp_reset_query(); ?>
    </ul>
    <?php endif; ?>

</div>

