<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class PayPalCheckout extends CompatibilityAbstract {
	public function is_available(): bool {
		return ( function_exists( 'wc_gateway_ppec' ) && ! empty( wc_gateway_ppec()->settings ) && method_exists( wc_gateway_ppec()->settings, 'is_enabled' ) && wc_gateway_ppec()->settings->is_enabled() );
	}

	public function run() {
		add_filter( 'woocommerce_checkout_posted_data', array( $this, 'set_billing_info_if_required' ), 10, 1 );
		add_filter( 'cfw_is_checkout', array( $this, 'is_checkout' ), 10, 1 );
	}

	public function is_checkout( $is_checkout ) {
		if ( wc_gateway_ppec()->checkout->has_active_session() ) {
			return false;
		}

		return $is_checkout;
	}

	public function set_billing_info_if_required( $data ) {
		if ( 'same_as_shipping' === $_POST['bill_to_different_address'] ) {
			foreach ( WC()->checkout()->get_checkout_fields( 'billing' ) as $key => $field ) {
				if ( 'billing_email' === $key ) {
					continue;
				}
				$data[ $key ] = $data[ 'shipping_' . substr( $key, 8 ) ] ?? '';
			}
		}

		return $data;
	}
}
