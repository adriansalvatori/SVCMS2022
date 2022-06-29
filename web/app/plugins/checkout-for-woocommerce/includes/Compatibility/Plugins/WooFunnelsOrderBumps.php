<?php

namespace Objectiv\Plugins\Checkout\Compatibility\Plugins;

use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;

class WooFunnelsOrderBumps extends CompatibilityAbstract {
	private $_positions = array(
		'cfw_woocommerce_checkout_order_review_above_payment_gateway' => array(
			'name'     => 'CheckoutWC: Above The Payment Gateways',
			'hook'     => 'cfw_checkout_payment_method_tab',
			'priority' => 7,
			'id'       => 'cfw_woocommerce_checkout_order_review_above_payment_gateway',
		),
		'cfw_woocommerce_checkout_order_review_below_payment_gateway' => array(
			'name'     => 'CheckoutWC: Below The Payment Gateways',
			'hook'     => 'cfw_checkout_payment_method_tab',
			'priority' => 12,
			'id'       => 'cfw_woocommerce_checkout_order_review_below_payment_gateway',
		),
	);

	public function is_available(): bool {
		return function_exists( 'WFOB_Core' );
	}

	public function pre_init() {
		add_filter( 'wfob_bump_positions', array( $this, 'add_cfw_positions' ) );
		add_action( 'wfob_position_not_found', array( $this, 'position_not_found' ), 10, 2 );
		add_action( 'wfob_position_fragment_not_found', array( $this, 'position_fragment_not_found' ), 10, 2 );
	}

	public function run() {
		add_action(
			'wp_head',
			function() {
				remove_all_actions( 'wfob_below_payment_gateway' );
			},
			1000
		);
	}

	public function add_cfw_positions( $positions ) {
		$positions = array_merge( $positions, $this->_positions );

		return $positions;
	}

	public function typescript_class_and_params( array $compatibility ): array {
		$compatibility[] = array(
			'class'  => 'WooFunnelsOrderBumps',
			'params' => array(),
		);

		return $compatibility;
	}

	public function position_not_found( $position_id, $position ) {
		add_action( $position['hook'], array( $this, $position_id ), $position['priority'] );
	}

	public function position_fragment_not_found( $position_id, $position ) {
		add_action( 'woocommerce_update_order_review_fragments', array( $this, $position_id . '_frg' ), $position['priority'] );
	}

	public function cfw_woocommerce_checkout_order_review_above_payment_gateway() {
		$this->print_placeholder( 'cfw_woocommerce_checkout_order_review_above_payment_gateway' );
	}

	public function cfw_woocommerce_checkout_order_review_above_payment_gateway_frg( $fragments ) {
		$WFOB_Public = \WFOB_Public::get_instance();
		$slug        = 'cfw_woocommerce_checkout_order_review_above_payment_gateway';

		return $WFOB_Public->get_bump_html( $fragments, $slug );
	}

	public function cfw_woocommerce_checkout_order_review_below_payment_gateway() {
		$this->print_placeholder( 'cfw_woocommerce_checkout_order_review_below_payment_gateway' );
	}

	public function cfw_woocommerce_checkout_order_review_below_payment_gateway_frg( $fragments ) {
		$WFOB_Public = \WFOB_Public::get_instance();
		$slug        = 'cfw_woocommerce_checkout_order_review_below_payment_gateway';

		return $WFOB_Public->get_bump_html( $fragments, $slug );
	}

	public function print_placeholder( $slug ) {
		$WFOB_Public = \WFOB_Public::get_instance();

		$html = '';
		if ( $WFOB_Public->show_on_load() ) {
			$html = $this->print_position_bump( $slug );
		}
		if ( apply_filters( 'wfob_print_placeholder', true, $slug ) ) {
			printf( "<div class='wfob_bump_wrapper %s'>%s</div>", $slug, $html );

		}
	}

	public function print_position_bump( $position ) {
		if ( empty( $position ) ) {
			return '';
		}

		ob_start();
		$bumps          = \WFOB_Bump_Fc::get_bumps();
		$shown_bump_ids = array();
		WC()->session->set( 'wfob_no_of_bump_shown', array() );
		if ( count( $bumps ) > 0 ) {
			/**
			 * @var $bump \WFOB_Bump
			 */
			foreach ( $bumps as $bump_id => $bump ) {
				$shown_bump_ids[] = $bump_id;
				if ( $bump->have_bumps() && $position == $bump->get_position() ) {

					$bump->print_bump();
				}
			}
		}
		WC()->session->set( 'wfob_no_of_bump_shown', $shown_bump_ids );

		return ob_get_clean();
	}
}
