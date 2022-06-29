<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooFinvoicer extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'WOO_FINVOICER_BASENAME' );
	}

	public function run() {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_business_id_field' ), 100, 1 );
	}

	/**
	 * @param array $fields
	 * @return array
	 */
	public function add_business_id_field( array $fields ) {
		if ( isset( $fields['billing']['billing_woo_finvoicer_business_id'] ) ) {
			$fields['billing']['billing_woo_finvoicer_business_id']['priority'] = 51; // after company field
			$fields['shipping']['shipping_woo_finvoicer_business_id'] = $fields['billing']['billing_woo_finvoicer_business_id'];
		}

		return $fields;
	}
}
