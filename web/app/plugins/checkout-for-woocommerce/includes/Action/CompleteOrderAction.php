<?php

namespace Objectiv\Plugins\Checkout\Action;

use Exception;

/**
 * Class CompleteOrderAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class CompleteOrderAction extends CFWAction {

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'add_cfw_meta_data' ) );

		parent::__construct( 'complete_order' );
	}

	/**
	 * Takes in the information from the order form and hands it off to Woocommerce.
	 *
	 * @throws Exception
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		wc_maybe_define_constant( 'WOOCOMMERCE_CHECKOUT', true );

		// If the user is logged in don't try and get the user from the front end, just get it on the back before we checkout
		if ( ! isset( $_POST['billing_email'] ) || ! $_POST['billing_email'] ) {
			$current_user = wp_get_current_user();
			if ( $current_user ) {
				$_POST['billing_email'] = $current_user->user_email;
			}
		}

		// Mark orders through CFW as being orders from CFW.
		$_POST['_cfw'] = true;

		/**
		 * Fires before checkout is processed in complete order action
		 *
		 * @since 3.0.0
		 */
		do_action( 'cfw_before_process_checkout' );

		WC()->checkout()->process_checkout();
		wp_die( 0 );
	}

	public function add_cfw_meta_data( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! empty( $_POST['_cfw'] ) ) {
			$order->add_meta_data( '_cfw', 'true', true );
		}

		if ( ! empty( $_POST['billing_full_name'] ) ) {
			$order->add_meta_data( '_billing_full_name', $_POST['billing_full_name'], true );
		}

		if ( ! empty( $_POST['shipping_full_name'] ) ) {
			$order->add_meta_data( '_shipping_full_name', $_POST['shipping_full_name'], true );
		}

		$order->save();
	}
}
