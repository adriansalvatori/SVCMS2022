<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WCFieldFactory extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'wcff' );
	}

	public function run() {
		$this->remove_filter();
	}

	public function remove_filter() {
		global $wp_filter;

		$existing_hooks = $wp_filter['woocommerce_checkout_fields'];

		$priority = 9;

		if ( $existing_hooks[ $priority ] ) {
			foreach ( $existing_hooks[ $priority ] as $key => $callback ) {
				if ( false !== stripos( $key, 'wcccf_filter_checkout_fields' ) ) {
					global $Wcff_CheckoutFields;

					$Wcff_CheckoutFields = $callback['function'][0];
				}
			}
		}

		if ( empty( $Wcff_CheckoutFields ) ) {
			return;
		}

		remove_filter( 'woocommerce_checkout_fields', array( $Wcff_CheckoutFields, 'wcccf_filter_checkout_fields' ), $priority );
	}
}
