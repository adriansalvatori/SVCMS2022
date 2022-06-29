<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class NIFPortugal extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'nif_active_nw_plugins' );
	}

	public function run() {
		add_filter( 'woocommerce_nif_field_class', '__return_empty_string' );
		add_filter( 'cfw_get_shipping_checkout_fields', array( $this, 'add_nif_field' ) );
	}

	public function add_nif_field( $fields ) {
		$billing_fields = WC()->checkout()->get_checkout_fields( 'billing' );

		$fields['shipping_nif'] = $billing_fields['billing_nif'];

		return $fields;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'NIFPortugal',
			'params' => array(),
		);

		return $compatibility;
	}
}
