<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Themes;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Flatsome extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'flatsome_fix_policy_text' );
	}

	public function run() {
		// Restore original ordering
		add_action( 'woocommerce_checkout_terms_and_conditions', 'wc_checkout_privacy_policy_text', 20 );
		remove_action( 'woocommerce_checkout_after_order_review', 'wc_checkout_privacy_policy_text', 1 );

		// Remove their fancy terms and conditions ish
		remove_action( 'woocommerce_checkout_terms_and_conditions', 'flatsome_terms_and_conditions_lightbox', 30 );
		remove_action( 'woocommerce_checkout_terms_and_conditions', 'flatsome_terms_and_conditions' );

		remove_action( 'wp_head', 'flatsome_custom_css', 100 );
	}
}
