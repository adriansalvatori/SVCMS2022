<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Vipps extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WOO_VIPPS_VERSION' );
	}

	public function run() {
		add_action( 'cfw_payment_request_buttons', array( $this, 'add_vipps_button' ) );
	}

	public function add_vipps_button() {
		$button = do_shortcode( '[woo_vipps_express_checkout_button]' );

		if ( ! empty( $button ) ) {
			echo $button;

			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
		}
	}
}