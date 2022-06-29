<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class BraintreeForWooCommerce extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'BFWC_PLUGIN_NAME' ) || defined( 'WC_BRAINTREE_PLUGIN_NAME' );
	}

	public function run() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'wc_braintree_banner_checkout_template' );
		add_action( 'cfw_payment_request_buttons', array( $this, 'render_banner_buttons' ) );
	}

	public function render_banner_buttons() {
		$gateways = array();
		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $id => $gateway ) {
			if ( $gateway->supports( 'wc_braintree_banner_checkout' ) && $gateway->banner_checkout_enabled() ) {
				$gateways[ $id ] = $gateway;
			}
		}
		if ( count( $gateways ) > 0 ) {
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );

			foreach ( $gateways as $gateway ) :?>
				<div class="wc-braintree-banner-gateway wc_braintree_banner_gateway_<?php echo esc_attr( $gateway->id ); ?>">
					<?php $gateway->banner_fields(); ?>
				</div>
				<?php
			endforeach;
		}
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'BraintreeForWooCommerce',
			'params' => array(),
		);

		return $compatibility;
	}
}
