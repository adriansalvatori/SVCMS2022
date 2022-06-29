<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CraftyClicks extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_CraftyClicks_Postcode_Lookup' );
	}

	public function run() {
		$all_integrations = WC()->integrations->get_integrations();

		if ( ! empty( $all_integrations['craftyclicks_postcode_lookup'] ) ) {
			$craftyclicks_postcode_lookup = $all_integrations['craftyclicks_postcode_lookup'];

			add_action( 'cfw_checkout_before_customer_info_tab', array( $craftyclicks_postcode_lookup, 'addCheckoutJs' ) );
		}
	}
}
