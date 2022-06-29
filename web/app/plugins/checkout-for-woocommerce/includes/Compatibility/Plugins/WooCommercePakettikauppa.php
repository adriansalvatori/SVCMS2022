<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommercePakettikauppa extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\Wc_Pakettikauppa' );
	}

	public function run() {
		remove_filter( 'woocommerce_checkout_fields', array( \Wc_Pakettikauppa::get_instance()->frontend, 'add_checkout_fields' ) );
		add_filter( 'woocommerce_checkout_fields', array( $this, 'add_shipping_email_field' ) );
	}

	public function add_shipping_email_field( $fields ) : array {
		$new_fields = \Wc_Pakettikauppa::get_instance()->frontend->add_checkout_fields( $fields );

		if ( isset( $new_fields['shipping']['shipping_email'] ) ) {
			$fields['shipping']['shipping_email'] = $new_fields['shipping']['shipping_email'];
		}

		return $fields;
	}
}
