<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooCommercePriceBasedOnCountry extends CompatibilityAbstract {
	public function is_available(): bool {
		return class_exists( '\\WCPBC_Frontend' );
	}

	public function pre_init() {
		add_action( 'wc_price_based_country_before_frontend_init', array( $this, 'maybe_set_country' ) );
	}

	public function maybe_set_country() {
		if ( defined( 'WC_DOING_AJAX' ) && WC_DOING_AJAX && isset( $_GET['wc-ajax'] ) && 'update_checkout' === $_GET['wc-ajax'] ) {
			$country   = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : false;
			$s_country = isset( $_POST['s_country'] ) ? wc_clean( wp_unslash( $_POST['s_country'] ) ) : false;

			if ( $country ) {
				wcpbc_set_prop_value( wc()->customer, 'billing_country', $country );
			}

			if ( wc_ship_to_billing_address_only() ) {
				if ( $country ) {
					WC()->customer->set_shipping_country( $country );
				}
			} else {
				if ( $s_country ) {
					WC()->customer->set_shipping_country( $s_country );
				}
			}
		}
	}
}
