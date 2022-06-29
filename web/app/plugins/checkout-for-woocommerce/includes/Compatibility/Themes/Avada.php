<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Avada extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'AVADA_VERSION' );
	}

	public function run() {
		// Prevents fusion styles from getting on page with Avada 7.5 and probably 7.4 and before
		// TODO: Test with last 3 major versions of Avada to see if we can get rid of the majority of the code in this file
		add_filter( 'fusion_dynamic_css_final', '__return_false' );
		add_filter( 'fusion_library_inline_dynamic_css', '__return_false' );
		add_filter( 'fusion_library_inline_custom_css', '__return_false' );

		add_filter(
			'avada_setting_get_css_cache_method',
			function() {
				return 'off';
			}
		);

		// Avada 7.3+ fixes
		if ( version_compare( AVADA_VERSION, '7.3.0', '>=' ) ) {
			add_action(
				'wp',
				function() {
					$this->checkout_page_fixes();
				},
				100
			);
		}

		// Avada 7.4 fixes
		if ( version_compare( AVADA_VERSION, '7.4.0', '>=' ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'fix_avada_74' ), 100 );
		}

		// 7.3 and below fixes
		if ( version_compare( AVADA_VERSION, '7.4.0', '<' ) ) {
			$this->checkout_page_fixes();

			add_action( 'wp_head', array( $this, 'cleanup_css' ), 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_css_new' ), 0 ); // latest Avada
			add_action( 'wp_body_open', array( $this, 'cleanup_css_new' ), 0 ); // latest Avada
		}

		// All Versions For Now
		$this->disable_lazy_loading();

		/** @var \Avada_Woocommerce */
		global $avada_woocommerce;

		if ( ! $avada_woocommerce ) {
			return;
		}

		// Remove actions
		remove_action( 'woocommerce_after_checkout_form', array( $avada_woocommerce, 'after_checkout_form' ) );
		remove_action( 'woocommerce_checkout_after_order_review', array( $avada_woocommerce, 'checkout_after_order_review' ), 20 );
		remove_action( 'woocommerce_checkout_before_customer_details', array( $avada_woocommerce, 'checkout_before_customer_details' ) );
		remove_action( 'woocommerce_checkout_after_customer_details', array( $avada_woocommerce, 'checkout_after_customer_details' ) );
		remove_action( 'woocommerce_checkout_billing', array( $avada_woocommerce, 'checkout_billing' ), 20 );
		remove_action( 'woocommerce_checkout_shipping', array( $avada_woocommerce, 'checkout_shipping' ), 20 );
	}

	public function checkout_page_fixes() {
		/** @var \Avada_Woocommerce */
		global $avada_woocommerce;

		if ( ! $avada_woocommerce ) {
			return;
		}

		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'avada_top_user_container' ), 1 );
		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'checkout_coupon_form' ), 10 );
		remove_action( 'woocommerce_before_checkout_form', array( $avada_woocommerce, 'before_checkout_form' ), 10 );
	}

	public function fix_avada_74() {

		/** @var \Fusion_Dynamic_CSS_Inline **/
		$inline_instance = \Fusion_Dynamic_CSS::get_instance()->inline;

		$loading_action = 'wp_head';
		$priority       = 999;

		if ( fusion_should_defer_styles_loading() ) {
			$loading_action = 'wp_print_footer_scripts';
			$priority       = 12;
		}

		if ( fusion_get_option( 'media_queries_async' ) ) {
			remove_action( 'wp_enqueue_scripts', array( $inline_instance, 'add_inline_css' ), 101 );
			remove_action( $loading_action, array( $inline_instance, 'add_custom_css_to_wp_head' ), $priority );
		} else {
			remove_action( $loading_action, array( $inline_instance, 'add_inline_css_wp_head' ), $priority );
		}
	}

	public function run_on_thankyou() {
		add_action( 'wp_head', array( $this, 'cleanup_css' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_css_new' ), 0 ); // latest Avada
		add_action( 'wp_body_open', array( $this, 'cleanup_css_new' ), 0 ); // latest Avada

		$this->disable_lazy_loading();

		/** @var \Avada_Woocommerce */
		global $avada_woocommerce;

		if ( ! $avada_woocommerce ) {
			return;
		}

		remove_action( 'woocommerce_thankyou', array( $avada_woocommerce, 'view_order' ), 20 );
		remove_action( 'woocommerce_view_order', array( $avada_woocommerce, 'view_order' ) );
		remove_action( 'woocommerce_thankyou', array( $avada_woocommerce, 'view_order' ) );
	}

	public function disable_lazy_loading() {
		if ( ! class_exists( '\\Fusion' ) ) {
			return;
		}

		$fusion = \Fusion::get_instance();
		remove_filter( 'wp_get_attachment_image_attributes', array( $fusion->images, 'lazy_load_attributes' ), 10 );
	}

	/**
	 * @deprecated
	 * @return void
	 */
	public function cleanup_css() {
		$fusion_dynamic_css_file = cfw_get_hook_instance_object( 'wp_head', 'add_inline_css_wp_head', 999 );

		if ( empty( $fusion_dynamic_css_file ) ) {
			return;
		}

		remove_action( \fusion_should_defer_styles_loading() ? 'wp_body_open' : 'wp_enqueue_scripts', array( $fusion_dynamic_css_file, 'add_inline_css' ) );
		remove_action( 'wp_head', array( $fusion_dynamic_css_file, 'add_custom_css_to_wp_head' ), 999 );
		remove_action( 'wp_head', array( $fusion_dynamic_css_file, 'add_inline_css_wp_head' ), 999 );
	}

	public function cleanup_css_new() {
		$object = cfw_get_hook_instance_object( \fusion_should_defer_styles_loading() ? 'wp_body_open' : 'wp_enqueue_scripts', 'enqueue_dynamic_css', 11 );

		if ( empty( $object ) ) {
			return;
		}

		remove_action( 'wp_enqueue_scripts', array( $object, 'enqueue_dynamic_css' ), 11 );
	}
}
