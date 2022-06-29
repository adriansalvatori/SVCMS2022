<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommercePensoPay extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'wc_pensopay_woocommerce_inactive_notice' );
	}

	public function run() {
		WC()->payment_gateways();

		global $wp_filter;

		$existing_hooks = $wp_filter['woocommerce_checkout_before_customer_details'];

		if ( $existing_hooks[10] ) {
			foreach ( $existing_hooks[10] as $key => $callback ) {
				if ( false !== stripos( $key, 'insert_woocommerce_pensopay_mobilepay_checkout' ) ) {
					global $WooCommercePensoPayMolliePay;

					$WooCommercePensoPayMolliePay = $callback['function'][0];
				}
			}
		}

		if ( ! empty( $WooCommercePensoPayMolliePay ) ) {
			remove_action( 'woocommerce_checkout_before_customer_details', array( $WooCommercePensoPayMolliePay, 'insert_woocommerce_pensopay_mobilepay_checkout' ), 10 );

			if ( method_exists( $WooCommercePensoPayMolliePay, 'is_gateway_available' ) && $WooCommercePensoPayMolliePay->is_gateway_available() ) {
				add_action( 'cfw_payment_request_buttons', array( $WooCommercePensoPayMolliePay, 'insert_woocommerce_pensopay_mobilepay_checkout' ), 10 );
				add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
			}
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooCommercePensoPay',
			'params' => array(),
		);

		return $compatibility;
	}
}
