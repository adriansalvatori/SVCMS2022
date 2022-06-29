<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

class EUVATAssistant {
	public function init() {
		add_filter( 'woocommerce_checkout_fields', array( $this, 'maybe_move_euvat_fields_to_order_step' ), 100, 1 );
	}

	public function maybe_move_euvat_fields_to_order_step( $fields ) {
		$cart = WC()->cart;

		if ( ! $cart || ! $cart->needs_shipping() ) {
			return $fields;
		}

		if ( ! class_exists( 'Aelia\WC\EU_VAT_Assistant\WC_Aelia_EU_VAT_Assistant' ) ) {
			return $fields;
		}

		if ( isset( $fields['billing']['vat_number'] ) ) {
			$this->move_field( 'vat_number', $fields['billing']['vat_number'] );
			unset( $fields['billing']['vat_number'] );
		}

		if ( isset( $fields['billing']['customer_location_self_certified'] ) ) {
			$this->move_field( 'customer_location_self_certified', $fields['billing']['customer_location_self_certified'] );
			unset( $fields['billing']['customer_location_self_certified'] );
		}

		return $fields;
	}

	protected function move_field( string $key, array $field ) {
		add_action(
			'cfw_checkout_payment_method_tab',
			function() use ( $key, $field ) {
				woocommerce_form_field( $key, $field );
			},
			25
		);
	}
}


