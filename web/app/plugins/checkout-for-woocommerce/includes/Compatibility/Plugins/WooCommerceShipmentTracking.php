<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommerceShipmentTracking extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WC_SHIPMENT_TRACKING_VERSION' );
	}

	public function run_on_thankyou() {
		$actions = \WC_Shipment_Tracking_Actions::get_instance();

		remove_action( 'woocommerce_view_order', array( $actions, 'display_tracking_info' ) );
	}
}
