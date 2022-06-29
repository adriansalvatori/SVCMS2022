<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class OneClickUpsells extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'GB_OCU_VER' );
	}

	public function run() {
		$this->add_ocu_checkout_buttons();
	}

	public function add_ocu_checkout_buttons() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();
		$add_sep  = false;

		if ( ! empty( $gateways['ocustripe'] ) ) {
			if ( $gateways['ocustripe']->apple_pay_enabled !== 'no' ) {
				add_action( 'cfw_checkout_before_customer_info_tab', 'gb_ocu_stripe_apple_pay_display_button', 5 );

				$add_sep = true;
			}
		}

		if ( ! empty( $gateways['ocupaypal'] ) ) {
			if ( $gateways['ocupaypal']->checkout_page === 'top' || $gateways['ocupaypal']->checkout_page === 'both' ) {
				add_action( 'cfw_checkout_before_customer_info_tab', array( $this, 'gb_ocu_paypal_display_button' ), 5 );

				$add_sep = true;
			}
		}

		if ( $add_sep ) {
			add_action( 'cfw_after_payment_request_buttons', 'cfw_add_separator', 11 );
		}
	}

	public function gb_ocu_paypal_display_button() {
		$gateways = WC()->payment_gateways->get_available_payment_gateways();

		if (
			! empty( $gateways['ocupaypal'] ) &&
			method_exists( $gateways['ocupaypal'], 'paypal_display_button' )
		) {
			$checkout_page = $gateways['ocupaypal']->checkout_page;

			if ( $checkout_page === 'top' || $checkout_page === 'both' ) {
				echo '<div class="ocu-woocommerce-info" style="text-align: center;">';

				$gateways['ocupaypal']->paypal_display_button();

				echo '</div>';
			}
		}
	}
}
