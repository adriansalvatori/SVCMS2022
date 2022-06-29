<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class EnhancedEcommerceGoogleAnalytics extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'wc_enhanced_ecommerce_google_analytics_add_integration' );
	}

	public function run() {
		$integrations = WC()->integrations->get_integrations();

		if ( isset( $integrations['enhanced_ecommerce_google_analytics'] ) ) {
			$wc_enhanced_ecommerce_google_analytics = $integrations['enhanced_ecommerce_google_analytics'];

			// Checkout Actions
			add_action( 'cfw_checkout_before_customer_info_tab', array( $wc_enhanced_ecommerce_google_analytics, 'checkout_step_1_tracking' ) );
			add_action( 'cfw_checkout_before_shipping_method_tab', array( $wc_enhanced_ecommerce_google_analytics, 'checkout_step_2_tracking' ) );
			add_action( 'cfw_checkout_before_payment_method_tab', array( $wc_enhanced_ecommerce_google_analytics, 'checkout_step_3_tracking' ) );
		}
	}
}
