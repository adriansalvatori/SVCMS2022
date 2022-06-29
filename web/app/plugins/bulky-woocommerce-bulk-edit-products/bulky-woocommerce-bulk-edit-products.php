<?php
/**
 * Plugin Name: Bulky - Bulk Edit Products for WooCommerce Premium
 * Plugin URI: https://villatheme.com/extensions/bulky-woocommerce-bulk-edit-products/
 * Description: WooCommerce Bulk Edit Products helps easily work with products in bulk. The plugin offers sufficient simple and advanced tools to help filter various available attributes of simple and variable products such as  ID, Title, Content, Excerpt, Slugs, SKU, Post date, range of regular price and sale price, Sale date, range of stock quantity, Product type, Categories.... Users can quickly search for wanted products fields and work with the product fields in bulk. The plugin promises to help users to save time and optimize manipulation when working with products in bulk.
 * Version: 1.2.0
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: bulky-woocommerce-bulk-edit-products
 * Domain Path: /languages
 * Copyright 2021-2022 VillaTheme.com. All rights reserved.
 * Requires at least: 5.0
 * Tested up to: 6.0
 * WC requires at least: 5.0
 * WC tested up to: 6.6
 * Requires PHP: 7.0
 **/

use WCBEditor\Includes\Admin\Admin;
use WCBEditor\Includes\Ajax;
use WCBEditor\Includes\Enqueue;
use WCBEditor\Support\Define_Support;
use WCBEditor\Includes\Abstracts\History_Abstract;

defined( 'ABSPATH' ) || exit;

if ( is_file( plugin_dir_path( __FILE__ ) . 'autoload.php' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'autoload.php';
}

class  Bulky_Products_Bulk_Editor {
	public $plugin_name = 'Bulky - Bulk Edit Products for WooCommerce';

	public $version = '1.2.0';

	public $conditional = '';

	protected static $instance = null;

	public function __construct() {
		$this->define();

		if ( ! ( $this->conditional = $this->check_conditional() ) ) {
			$this->load_class();
			add_filter( 'plugin_action_links_' . WCBE_CONST['basename'], [ $this, 'setting_link' ] );
			add_action( 'init', [ $this, 'load_text_domain' ] );
		}

		add_action( 'admin_notices', [ $this, 'admin_notices' ] );

		register_activation_hook( __FILE__, [ $this, 'active' ] );

	}

	public static function instance() {
		return self::$instance == null ? self::$instance = new self : self::$instance;
	}

	public function define() {
		define( 'WCBE_CONST', [
			'version'      => $this->version,
			'slug'         => 'bulky-woocommerce-bulk-edit-products',
			'assets_slug'  => 'bulky-woocommerce-bulk-edit-products-',
			'file'         => __FILE__,
			'basename'     => plugin_basename( __FILE__ ),
			'plugin_dir'   => plugin_dir_path( __FILE__ ),
			'includes_dir' => plugin_dir_path( __FILE__ ) . 'includes' . DIRECTORY_SEPARATOR,
			'admin_dir'    => plugin_dir_path( __FILE__ ) . 'admin' . DIRECTORY_SEPARATOR,
			'dist_dir'     => plugin_dir_path( __FILE__ ) . 'assets' . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR,
			'dist_url'     => plugins_url( 'assets/dist/', __FILE__ ),
			'libs_url'     => plugins_url( 'assets/libs/', __FILE__ ),
			'img_url'      => plugins_url( 'assets/img/', __FILE__ ),
			'capability'   => 'edit_products'
		] );
	}

	public function admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! $this->conditional ) {
			return;
		}
		foreach ( $this->conditional as $message ) {
			echo sprintf( "<div id='message' class='error'><p>%s</p></div>", esc_html( $message ) );
		}
	}

	public function setting_link( $links ) {
		$editor_link   = [ sprintf( "<a href='%1s' >%2s</a>", esc_url( admin_url( 'admin.php?page=vi_wbe_edit_products' ) ), esc_html__( 'Editor', 'bulky-woocommerce-bulk-edit-products' ) ) ];
		$settings_link = [ sprintf( "<a href='%1s' >%2s</a>", esc_url( admin_url( 'admin.php?page=vi_wbe_settings' ) ), esc_html__( 'Settings', 'bulky-woocommerce-bulk-edit-products' ) ) ];

		return array_merge( $editor_link, $settings_link, $links );
	}

	public function check_conditional() {
		$message = [];
		if ( ! version_compare( phpversion(), '7.0', '>=' ) ) {
			$message[] = $this->plugin_name . ' ' . esc_html__( 'require PHP version at least 7.0', 'bulky-woocommerce-bulk-edit-products' );
		}

		global $wp_version;
		if ( ! version_compare( $wp_version, '5.0', '>=' ) ) {
			$message[] = $this->plugin_name . ' ' . esc_html__( 'require WordPress version at least 5.0', 'bulky-woocommerce-bulk-edit-products' );
		}

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$message[] = $this->plugin_name . ' ' . esc_html__( 'require WooCommerce is activated', 'bulky-woocommerce-bulk-edit-products' );
		}

		$vvc_version = get_option( 'woocommerce_version' );
		if ( ! ( $vvc_version && version_compare( $vvc_version, '4.0', '>=' ) ) ) {
			$message[] = $this->plugin_name . ' ' . esc_html__( 'require WooCommerce version at least 4.0', 'bulky-woocommerce-bulk-edit-products' );
		}

		return $message;
	}

	public function load_class() {
		if ( is_admin() ) {
			Enqueue::instance();
			Define_Support::instance();
			Admin::instance();
			Ajax::instance();
		}
	}

	public function load_text_domain() {
		$locale = determine_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'bulky-woocommerce-bulk-edit-products' );

		unload_textdomain( 'bulky-woocommerce-bulk-edit-products' );
		load_textdomain( 'bulky-woocommerce-bulk-edit-products', WP_LANG_DIR . '/bulky-woocommerce-bulk-edit-products/bulky-woocommerce-bulk-edit-products-' . $locale . '.mo' );
		load_plugin_textdomain( 'bulky-woocommerce-bulk-edit-products', false, plugin_basename( dirname( WCBE_CONST['file'] ) ) . '/languages' );
	}

	public function active( $network_wide ) {
		global $wpdb;
		$history = History_Abstract::instance();
		if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
			$current_blog = $wpdb->blogid;
			$blogs        = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog );
				$history->create_database_table();
			}
			switch_to_blog( $current_blog );
		} else {
			$history->create_database_table();
		}
	}

}

Bulky_Products_Bulk_Editor::instance();

