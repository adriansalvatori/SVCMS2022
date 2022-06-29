<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class In3 extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'Woosa_IN3\PREFIX' );
	}

	public function pre_init() {
		add_action( 'cfw_get_payment_methods_html', array( $this, 'override_shipping_address_flag' ), 10 );
		add_action( 'cfw_before_process_checkout', array( $this, 'override_shipping_address_flag' ), 10 );
	}

	public function override_shipping_address_flag() {
		if ( isset( $_POST['bill_to_different_address'] ) && 'same_as_shipping' === $_POST['bill_to_different_address'] ) {
			$_POST['ship_to_different_address'] = 0;

			if ( isset( $_POST['post_data'] ) ) {
				$post_data = array();

				parse_str( $_POST['post_data'], $post_data );

				$post_data['ship_to_different_address'] = 0;

				$_POST['post_data'] = http_build_query( $post_data );
			}
		}
	}
}
