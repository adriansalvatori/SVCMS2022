<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class ActiveCampaign extends CompatibilityAbstract {
	public function is_available(): bool {
		return function_exists( 'activecampaign_for_woocommerce_build_container' );
	}

	public function run() {
		global $wp_filter;

		$existing_hooks = $wp_filter['woocommerce_after_checkout_billing_form'];

		if ( $existing_hooks[10] ) {
			foreach ( $existing_hooks[10] as $key => $callback ) {
				if ( false !== stripos( $key, 'handle_woocommerce_checkout_form' ) ) {
					global $ActiveCampaign_Public;

					$ActiveCampaign_Public = $callback['function'][0];
				}
			}
		}

		if ( ! empty( $ActiveCampaign_Public ) ) {
			/**
			 * Filters hook to render Active Campaign checkbox output
			 *
			 * @since 2.0.0
			 *
			 * @param string $render_on The action hook to render on
			 */
			$render_on = apply_filters( 'cfw_active_campaign_checkbox_hook', 'cfw_checkout_before_payment_method_tab_nav' );

			add_action( $render_on, array( $ActiveCampaign_Public, 'handle_woocommerce_checkout_form' ) );
		}
	}
}
