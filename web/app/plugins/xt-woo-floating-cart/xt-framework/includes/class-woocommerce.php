<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'XT_Framework_Woocommerce' ) ) {

    class XT_Framework_Woocommerce {

        public static function wrap_product_images() {

            if(did_action('xtfw_wc_wrapped_product_images')) {
                return;
            }

            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_styles'));

            // Flatsome theme
            if(XT_Framework::first_instance()->is_theme('Flatsome')) {
                add_action('flatsome_woocommerce_shop_loop_images', array(__CLASS__, 'template_loop_before_product_thumbnail'), 9);
                add_action('flatsome_woocommerce_shop_loop_images', array(__CLASS__, 'template_loop_after_product_thumbnail'), 12);

            // Other themes
            }else {
                add_action('woocommerce_before_shop_loop_item_title', array(__CLASS__, 'template_loop_before_product_thumbnail'), 9);
                add_action('woocommerce_before_shop_loop_item_title', array(__CLASS__, 'template_loop_after_product_thumbnail'), 11);
            }

            do_action('xtfw_wc_wrapped_product_images');

        }

        public static function template_loop_before_product_thumbnail() {

            $classes = apply_filters('xtfw_wc_product_image_wrapper_classes', array('xtfw-wc-product-image'));
            $classes = implode(" ", $classes);

            do_action('xtfw_wc_above_product_image');
            echo '<div class="'.esc_attr($classes).'">';
            do_action('xtfw_wc_before_product_image');

        }

        public static function template_loop_after_product_thumbnail() {

            do_action('xtfw_wc_after_product_image');
            echo '</div>';
            do_action('xtfw_wc_below_product_image');
        }

        public static function enqueue_styles() {

            wp_add_inline_style('xtfw-inline', '
            .xtfw-wc-product-image{
                position: relative;
            }
            ');
        }

    }
}