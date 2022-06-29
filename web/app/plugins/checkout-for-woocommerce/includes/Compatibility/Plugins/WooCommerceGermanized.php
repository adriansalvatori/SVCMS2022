<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceGermanized extends CompatibilityAbstract {
	protected $render_on_order_review_step;

	public function setup( bool $render_on_order_review_step ): self {
		$this->render_on_order_review_step = $render_on_order_review_step;

		return $this;
	}
	public function is_available(): bool {
		return function_exists( 'WC_germanized' );
	}

	public function pre_init() {
		/**
		 * Don't monkey around with gateways
		 */
		add_filter( 'woocommerce_gzd_compatibilities', array( $this, 'override_ppec_compat' ), 1000, 1 );
	}

	public function run() {
		/**
		 * Don't let WooCommerce Germanized Eff Up the Submit Button
		 */
		$wc_gzd_checkout = \WC_GZD_Checkout::instance();
		remove_filter( 'woocommerce_update_order_review_fragments', array( $wc_gzd_checkout, 'refresh_order_submit' ), 150 );
		remove_action( 'woocommerce_review_order_before_submit', 'woocommerce_gzd_template_set_order_button_remove_filter', PHP_INT_MAX );
		remove_action( 'woocommerce_review_order_after_submit', 'woocommerce_gzd_template_set_order_button_show_filter', PHP_INT_MAX );
		remove_action( 'woocommerce_gzd_review_order_before_submit', 'woocommerce_gzd_template_set_order_button_show_filter', PHP_INT_MAX );

		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_gzd_template_order_submit', wc_gzd_get_hook_priority( 'checkout_order_submit' ) );
		remove_action( 'woocommerce_checkout_after_order_review', 'woocommerce_gzd_template_order_submit_fallback', 50 );
		remove_action( 'woocommerce_review_order_before_cart_contents', 'woocommerce_gzd_template_checkout_table_content_replacement' );

		remove_action( 'woocommerce_review_order_after_payment', 'woocommerce_gzd_template_render_checkout_checkboxes', 10 );
		remove_action( 'woocommerce_review_order_after_payment', 'woocommerce_gzd_template_checkout_set_terms_manually', wc_gzd_get_hook_priority( 'checkout_set_terms' ) );
	}

	public function run_immediately() {
		if ( $this->render_on_order_review_step ) {
			add_action( 'cfw_checkout_order_review_tab', 'woocommerce_gzd_template_render_checkout_checkboxes', 41 );
			add_action( 'cfw_checkout_order_review_tab', 'woocommerce_gzd_template_checkout_set_terms_manually', 41 );
		} else {
			add_action( 'cfw_checkout_before_payment_method_tab_nav', 'woocommerce_gzd_template_render_checkout_checkboxes' );
			add_action( 'cfw_checkout_before_payment_method_tab_nav', 'woocommerce_gzd_template_checkout_set_terms_manually' );
		}
	}

	public function override_ppec_compat( $plugins ) {
		if ( isset( $plugins['woocommerce-gateway-paypal-express-checkout'] ) ) {
			unset( $plugins['woocommerce-gateway-paypal-express-checkout'] );
		}

		if ( isset( $plugins['woocommerce-paypal-payments'] ) ) {
			unset( $plugins['woocommerce-paypal-payments'] );
		}

		return $plugins;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooCommerceGermanized',
			'params' => array(),
		);

		return $compatibility;
	}
}
