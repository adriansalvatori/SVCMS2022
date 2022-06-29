<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class UltimateRewardsPoints extends CompatibilityAbstract {
	public function is_available(): bool {
		return defined( 'MWB_WPR_DIRPATH' );
	}

	public function run() {
		// Add earn points and save message
		// This is necessary because WooCommerce Ultimate Rewards uses a WooCommerce native checkout
		// template override
		add_action( 'cfw_checkout_before_form', array( $this, 'render_earn_points_message' ) );
	}

	public function render_earn_points_message() {
		$user_id = get_current_user_ID();
		if ( isset( $user_id ) && ! empty( $user_id ) ) {

			$get_points               = (int) get_user_meta( $user_id, 'mwb_wpr_points', true );
			$mwb_wpr_cart_points_rate = get_option( 'mwb_wpr_cart_points_rate', 1 );
			$mwb_wpr_cart_price_rate  = get_option( 'mwb_wpr_cart_price_rate', 1 );
			$conversion               = ( $get_points * $mwb_wpr_cart_price_rate / $mwb_wpr_cart_points_rate ); ?>
			<div class="custom_point_checkout woocommerce-info">
				<input type="number" name="mwb_cart_points" class="input-text" id="mwb_cart_points" value="" placeholder="<?php cfw_esc_attr_e( 'Points', 'woocommerce-ultimate-points-and-rewards' ); ?>"/>
				<input type="button" name="mwb_cart_points_apply" data-point="<?php echo $get_points; ?>" data-id="<?php echo $user_id; ?>"" class="button mwb_cart_points_apply" id="mwb_cart_points_apply" value="<?php cfw_e( 'Apply Points', 'woocommerce-ultimate-points-and-rewards' ); ?>"/>
				<p><?php echo $get_points . cfw__( ' Points', 'woocommerce-ultimate-points-and-rewards' ) . ' = ' . wc_price( $conversion ); ?></p>
			</div>

			<?php
		}
	}
}
