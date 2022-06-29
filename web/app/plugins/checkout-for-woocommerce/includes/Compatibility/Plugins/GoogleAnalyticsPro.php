<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class GoogleAnalyticsPro extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'wc_google_analytics_pro' );
	}

	public function run() {
		$wc_google_analytics_pro             = wc_google_analytics_pro();
		$wc_google_analytics_pro_integration = $wc_google_analytics_pro->get_integration();

		// selected payment method
		if ( $wc_google_analytics_pro_integration->has_event( 'selected_payment_method' ) ) {
			add_action( 'cfw_checkout_after_payment_methods', array( $wc_google_analytics_pro_integration, 'selected_payment_method' ) );
		}
	}
}
