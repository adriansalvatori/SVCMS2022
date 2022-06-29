<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class CountryBasedPayments extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WoocommerceCountryBasedPayment' );
	}

	public function run_immediately() {
		// check if ajax request
		if ( ! is_admin() && ( isset( $_REQUEST['wc-ajax'] ) && 'update_checkout' === $_REQUEST['wc-ajax'] ) ) {
			// Fix WPML WooCommerce Multilingual error
			add_filter( 'wcml_supported_currency_payment_gateways', array( $this, 'availablePaymentGateways' ), 90, 1 );
			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'availablePaymentGateways' ), 10, 1 );
		}
	}

	/**
	 * List through available payment gateways,
	 * check if certain payment gateway is enabled for country,
	 * if no, unset it from $payment_gateways array
	 *
	 * @param $payment_gateways
	 * @return array with updated list of available payment gateways
	 */
	public function availablePaymentGateways( $payment_gateways ): array {

		foreach ( $payment_gateways as $key => $value ) {
			// check if WCML array
			$gateway_id           = ( is_object( $value ) && isset( $value->id ) ) ? $value->id : $key;
			$gateway_availability = get_option( 'wccbp' . '_' . $gateway_id );

			if ( ! empty( $gateway_availability ) && ! in_array( $_REQUEST['country'], $gateway_availability, true ) ) {
				unset( $payment_gateways[ $gateway_id ] );
			}
		}
		return $payment_gateways;
	}
}
