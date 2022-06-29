<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Gateways;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class Braintree extends CompatibilityAbstract {

	/**
	 * @var array
	 * @private
	 */
	private $braintree_gateways_available;

	public function is_available(): bool {
		$available = false;
		if ( function_exists( 'wc_braintree' ) ) {
			$braintree      = wc_braintree();
			$cc_gateway     = $braintree->get_gateway( \WC_Braintree::CREDIT_CARD_GATEWAY_ID );
			$paypal_gateway = $braintree->get_gateway( \WC_Braintree::PAYPAL_GATEWAY_ID );

			$this->set_braintree_gateways_available(
				array(
					'cc'     => $cc_gateway->is_available(),
					'paypal' => $paypal_gateway->is_available(),
				)
			);

			if ( $cc_gateway->is_available() || $paypal_gateway->is_available() ) {
				$available = true;
			}
		}

		return $available;
	}

	public function run() {
		remove_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 10 );
		add_action( 'cfw_checkout_payment_method_tab', 'cfw_payment_methods', 25 );
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$braintree_gateways_available = $this->get_braintree_gateways_available();

		$compatibility[] = array(
			'class'  => 'Braintree',
			'params' => array(
				'cc_gateway_available'     => $braintree_gateways_available['cc'],
				'paypal_gateway_available' => $braintree_gateways_available['paypal'],
			),
		);

		return $compatibility;
	}

	/**
	 * @return array
	 */
	public function get_braintree_gateways_available(): array {
		return $this->braintree_gateways_available;
	}

	/**
	 * @param array $braintree_gateways_available
	 */
	public function set_braintree_gateways_available( array $braintree_gateways_available ) {
		$this->braintree_gateways_available = $braintree_gateways_available;
	}
}
