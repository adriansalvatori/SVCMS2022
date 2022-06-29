<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_Admin_Admin {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		add_filter(
			'plugin_action_links_woocommerce-product-variations-swatches/woocommerce-product-variations-swatches.php', array(
				$this,
				'settings_link'
			)
		);
	}

	public function settings_link( $links ) {
		$settings_link = sprintf( '<a href="%s?page=woocommerce-product-variations-swatches" title="%s">%s</a>', esc_url( admin_url( 'admin.php' ) ),
			esc_attr__( 'Settings', 'woocommerce-product-variations-swatches' ),
			esc_html__( 'Settings', 'woocommerce-product-variations-swatches' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-product-variations-swatches' );
		load_textdomain( 'woocommerce-product-variations-swatches', VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_LANGUAGES . "woocommerce-product-variations-swatches-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-product-variations-swatches', false, VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_LANGUAGES );

	}

	public function init() {
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-product-variations-swatches/',
					'docs'      => 'http://docs.villatheme.com/?item=woo-product-variations-swatches',
					'review'    => 'https://codecanyon.net/downloads',
					'css'       => VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_CSS,
					'image'     => VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_IMAGES,
					'slug'      => 'woocommerce-product-variations-swatches',
					'menu_slug' => 'woocommerce-product-variations-swatches',
					'version'   => VIWPVS_WOOCOMMERCE_PRODUCT_VARIATIONS_SWATCHES_VERSION
				)
			);
		}
	}
}