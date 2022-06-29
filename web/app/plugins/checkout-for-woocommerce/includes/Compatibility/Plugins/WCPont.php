<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WCPont extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WC_Pont' );
	}

	public function run_immediately() {
		global $wc_pont;

		if ( ! empty( $wc_pont ) ) {
			remove_action( 'woocommerce_review_order_before_payment', array( $wc_pont, 'wc_pont_html' ), 1 );
			add_action( 'cfw_checkout_shipping_method_tab', array( $wc_pont, 'wc_pont_html' ), 21 );
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WCPont',
			'params' => array(),
		);

		return $compatibility;
	}
}
