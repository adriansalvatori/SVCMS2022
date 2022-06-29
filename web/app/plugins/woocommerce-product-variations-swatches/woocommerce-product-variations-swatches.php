<?php
/**
 * Plugin Name: WooCommerce Product Variations Swatches Premium
 * Plugin URI: https://villatheme.com/extensions/woocommerce-product-variations-swatches/
 * Description: WooCommerce Product Variations Swatches is a professional plugin that allows you to show and select attributes for variation products. The plugin displays variation select options of the products under colors, buttons, images, variation images, radio so it helps the customers observe the products they need more visually, save time to find the wanted products than dropdown type for variations of a variable product.
 * Version: 1.0.10
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-product-variations-swatches
 * Domain Path: /languages
 * Copyright 2020-2022 VillaTheme.com. All rights reserved.
 * Tested up to: 6.0
 * WC requires at least: 5.0
 * WC tested up to: 6.6
 * Requires PHP: 7.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION', '1.0.10' );
/**
 * Detect plugin. For use on Front End only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-product-variations-swatches" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
}

/**
 * Class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES
 */
class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES {
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'global_note' ) );
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );
	}

	/**
	 * Notify if WooCommerce is not activated
	 */
	public function global_note() {

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			?>
            <div id="message" class="error">
                <p><?php esc_html_e( 'Please install and activate WooCommerce to use WooCommerce Product Variations Swatches.', 'woocommerce-product-variations-swatches' ); ?></p>
            </div>
			<?php
		}
	}

	public function activated_plugin( $plugin ) {
		if ( $plugin === 'woocommerce-product-variations-swatches/woocommerce-product-variations-swatches.php' && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$taxonomies = wc_get_attribute_taxonomies();
			$json_data  = '{"ids":["vi_wpvs_button_design","vi_wpvs_color_design","vi_wpvs_image_design"],"names":["Button Design","Color Design","Image Design"],"attribute_reduce_size_mobile":["85","85","85"],"attribute_reduce_size_list_product":["75","85","75"],"attribute_width":[false,"32","50"],"attribute_height":[false,"32","50"],"attribute_fontsize":["13","13","13"],"attribute_padding":["10px 20px","3px","4px"],"attribute_transition":["30","30","30"],"attribute_default_box_shadow_color":[false,"rgba(238, 238, 238, 1)",false],"attribute_default_color":["rgba(33, 33, 33, 1)",false,false],"attribute_default_bg_color":["#ffffff","rgba(0, 0, 0, 0)","rgba(255, 255, 255, 1)"],"attribute_default_border_color":["#cccccc","rgba(255, 255, 255, 0)","rgba(238, 238, 238, 1)"],"attribute_default_border_radius":[false,"20",false],"attribute_default_border_width":["1","0","1"],"attribute_hover_scale":["1","1","1"],"attribute_hover_box_shadow_color":[false,false,false],"attribute_hover_color":["rgba(255, 255, 255, 1)",false,false],"attribute_hover_bg_color":["rgba(33, 33, 33, 1)","rgba(0, 0, 0, 0)",false],"attribute_hover_border_color":["rgba(33, 33, 33, 1)","rgba(0, 0, 0, 1)","rgba(33, 33, 33, 1)"],"attribute_hover_border_radius":[false,"20",false],"attribute_hover_border_width":["1","1","1"],"attribute_selected_scale":["1","1","1"],"attribute_selected_icon_enable":[],"attribute_selected_icon_type":[],"attribute_selected_icon_color":[],"attribute_selected_box_shadow_color":[false,false,false],"attribute_selected_color":["rgba(255, 255, 255, 1)",false,false],"attribute_selected_bg_color":["rgba(33, 33, 33, 1)","rgba(255, 255, 255, 0)",false],"attribute_selected_border_color":["rgba(33, 33, 33, 1)","rgba(0, 0, 0,1)","rgba(33, 33, 33, 1)"],"attribute_selected_border_radius":[false,"20",false],"attribute_selected_border_width":["1","1","1"],"attribute_out_of_stock":["blur","blur","blur"],"attribute_tooltip_enable":[false,false,false],"attribute_tooltip_type":[],"attribute_tooltip_position":["top","top","top"],"attribute_tooltip_width":[],"attribute_tooltip_height":[],"attribute_tooltip_fontsize":["14","14","14"],"attribute_tooltip_border_radius":["3","3","3"],"attribute_tooltip_bg_color":["#ffffff","#ffffff","#ffffff"],"attribute_tooltip_color":["#222222","#222222","#222222"],"attribute_tooltip_border_color":["#cccccc","#cccccc","#cccccc"],"attribute_display_default":"button","attribute_double_click":"","taxonomy_profiles":[],"taxonomy_loop_enable":[],"taxonomy_display_type":[],"custom_attribute_id":["1584610177"],"custom_attribute_name":[false],"custom_attribute_category":[],"custom_attribute_type":["button"],"custom_attribute_profiles":["variationswatchesdesign"],"custom_attribute_loop_enable":[false],"custom_attribute_display_type":["vertical"],"product_list_assign":"","product_list_add_to_cart":"","product_list_position":"after_price","product_list_align":"left","product_list_double_click_enable":"1","product_list_tooltip_enable":"","product_list_attr_name_enable":"","product_list_maximum_attr_item":"0","product_list_more_link_enable":"1","product_list_maximum_more_link_text":"{link_more_icon}","custom_css":"","purchased_code":"","check_swatches_settings":1}';
			if ( ! get_option( 'vi_woo_product_variation_swatches_params', '' ) ) {
				update_option( 'vi_woo_product_variation_swatches_params', json_decode( $json_data, true ) );
				if ( $taxonomies ) {
					exit( wp_redirect( admin_url( 'admin.php?page=woocommerce-product-variations-swatches-global-attrs' ) ) );
				}
			}
			$vi_wpvs_settings = get_option( 'vi_woo_product_variation_swatches_params', array() );
			if ( $taxonomies && ( empty( $vi_wpvs_settings ) || empty( $vi_wpvs_settings['check_swatches_settings'] ) ) ) {
				$vi_wpvs_settings['check_swatches_settings'] = 1;
				update_option( 'vi_woo_product_variation_swatches_params', $vi_wpvs_settings );
				exit( wp_redirect( admin_url( 'admin.php?page=woocommerce-product-variations-swatches-global-attrs' ) ) );
			}
		}
	}
}

new VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES();