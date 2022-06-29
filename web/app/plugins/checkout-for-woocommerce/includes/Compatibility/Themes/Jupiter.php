<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Jupiter extends CompatibilityAbstract {
	public function is_available(): bool {
		$theme = wp_get_theme();

		return 'jupiter' === $theme->template && class_exists( '\\MK_Customizer' ) && class_exists( '\\ReflectionFunction' );
	}

	public function run() {
		$this->unset_theme_callbacks( 'woocommerce_after_checkout_billing_form' );
		$this->unset_theme_callbacks( 'woocommerce_check_cart_items' );
		$this->unset_theme_callbacks( 'woocommerce_review_order_before_payment' );
		$this->unset_theme_callbacks( 'woocommerce_review_order_before_submit' );
		$this->unset_theme_callbacks( 'woocommerce_check_cart_items', 9 );

		// Reverse their other stuff too
		add_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review' );
		remove_action( 'woocommerce_checkout_shipping', 'woocommerce_order_review', 10 );

		add_action( 'woocommerce_checkout_shipping', array( \WC_Checkout::instance(), 'checkout_form_shipping' ) );
		remove_action( 'woocommerce_checkout_billing', array( \WC_Checkout::instance(), 'checkout_form_shipping' ) );

	}

	function unset_theme_callbacks( $hook, $priority = 10 ) {
		global $wp_filter;

		$existing_hooks = $wp_filter[ $hook ];

		if ( $existing_hooks[ $priority ] ) {
			foreach ( $existing_hooks[ $priority ] as $key => $callback ) {
				if ( is_array( $callback['function'] ) ) {
					continue;
				}

				try {
					$ref = new \ReflectionFunction( $callback['function'] );

					if ( stripos( $ref->getFileName(), get_template_directory() ) !== false ) {
						remove_action( $hook, $callback['function'], $priority );
						unset( $wp_filter[ $hook ][ $priority ][ $key ] );
					}
				} catch ( \Exception $e ) {
					error_log( 'CheckoutWC: Failed to unset Jupiter theme callbacks.' );
				}
			}
		}
	}
}
